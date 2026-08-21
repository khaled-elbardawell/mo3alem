<?php

namespace App\Http\Controllers\Site;

use App\AdPlacement;
use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\SeoPage;
use App\Services\AdCampaignSelector;
use App\Services\MetricService;
use App\Services\SeoManager;
use App\Services\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

use function Illuminate\Support\defer;

class CertificateToolController extends Controller
{
    public function __invoke(
        Request $request,
        AdCampaignSelector $ads,
        MetricService $metrics,
        SeoManager $seoManager,
        VisitorIdentity $visitors,
    ): View {
        $visitorIdentifier = $visitors->for($request);
        defer(fn () => $metrics->recordSiteVisit($visitorIdentifier));

        $loadedCertificate = null;

        if ($request->filled('certificate') && $request->user()) {
            $loadedCertificate = Certificate::query()->findOrFail($request->integer('certificate'));
            Gate::authorize('view', $loadedCertificate);
            $loadedCertificate->forceFill(['last_opened_at' => now()])->save();
        }

        $seo = $seoManager->forPage(SeoPage::Certificates);
        $campaigns = [
            'top' => $ads->select(AdPlacement::Top),
            'side' => $ads->select(AdPlacement::Side),
            'bottom' => $ads->select(AdPlacement::Bottom),
        ];
        $templates = CertificateTemplate::query()->active()->ordered()->get();
        $savedTemplateKey = $loadedCertificate?->template_key;

        if ($savedTemplateKey && $savedTemplateKey !== 'custom' && ! $templates->contains('key', $savedTemplateKey)) {
            $savedTemplate = CertificateTemplate::withTrashed()->where('key', $savedTemplateKey)->first();

            if ($savedTemplate) {
                $templates->push($savedTemplate);
            }
        }

        $certificateConfig = [
            'authenticated' => (bool) $request->user(),
            'verified' => $request->user()?->hasVerifiedEmail() ?? false,
            'limits' => [
                'savedCertificates' => (int) config('resource_limits.certificates', 5),
            ],
            'usage' => [
                'savedCertificates' => $request->user()?->certificates()->count() ?? 0,
            ],
            'savedCertificate' => $loadedCertificate
                ? CertificateResource::make($loadedCertificate)->resolve($request)
                : null,
            'templates' => $templates->map->toToolConfig()->all(),
            'routes' => [
                'metrics' => route('activity-metrics.store'),
                'store' => $request->user() ? route('certificates.store') : null,
                'updateBase' => $request->user() ? url('/certificates') : null,
                'login' => route('tools.certificates.auth', 'login'),
                'register' => route('tools.certificates.auth', 'register'),
                'dashboard' => $request->user() ? route('dashboard', ['section' => 'certificates']) : null,
                'verification' => $request->user() ? route('verification.notice') : null,
            ],
            'csrfToken' => csrf_token(),
        ];

        return view('public.tools.certificates', compact('seo', 'campaigns', 'certificateConfig'));
    }
}
