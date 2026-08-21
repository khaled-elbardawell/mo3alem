<?php

namespace App\Http\Controllers\Site;

use App\AdPlacement;
use App\Http\Controllers\Controller;
use App\Http\Resources\QrCodeResource;
use App\Models\QrCode;
use App\Models\QrTemplate;
use App\SeoPage;
use App\Services\AdCampaignSelector;
use App\Services\MetricService;
use App\Services\SeoManager;
use App\Services\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

use function Illuminate\Support\defer;

class QrToolController extends Controller
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

        $loadedQrCode = null;

        if ($request->filled('qr') && $request->user()) {
            $loadedQrCode = QrCode::query()->findOrFail($request->integer('qr'));
            Gate::authorize('view', $loadedQrCode);
            $loadedQrCode->forceFill(['last_opened_at' => now()])->save();
        }

        $seo = $seoManager->forPage(SeoPage::Qr);
        $campaigns = [
            'top' => $ads->select(AdPlacement::Top),
            'side' => $ads->select(AdPlacement::Side),
            'bottom' => $ads->select(AdPlacement::Bottom),
        ];
        $templates = QrTemplate::query()->active()->ordered()->get();
        $savedTemplateKey = data_get($loadedQrCode?->design, 'frame');

        if ($savedTemplateKey && $savedTemplateKey !== 'none' && ! $templates->contains('key', $savedTemplateKey)) {
            $savedTemplate = QrTemplate::withTrashed()->where('key', $savedTemplateKey)->first();

            if ($savedTemplate) {
                $templates->push($savedTemplate);
            }
        }

        $qrConfig = [
            'authenticated' => (bool) $request->user(),
            'verified' => $request->user()?->hasVerifiedEmail() ?? false,
            'limits' => [
                'savedQrCodes' => (int) config('resource_limits.qr_codes', 5),
            ],
            'usage' => [
                'savedQrCodes' => $request->user()?->qrCodes()->count() ?? 0,
            ],
            'savedQrCode' => $loadedQrCode
                ? QrCodeResource::make($loadedQrCode)->resolve($request)
                : null,
            'templates' => $templates->map->toToolConfig()->all(),
            'routes' => [
                'render' => route('tools.qr.render'),
                'metrics' => route('activity-metrics.store'),
                'store' => $request->user() ? route('qr-codes.store') : null,
                'updateBase' => $request->user() ? url('/qr-codes') : null,
                'login' => route('tools.qr.auth', 'login'),
                'register' => route('tools.qr.auth', 'register'),
                'dashboard' => $request->user() ? route('dashboard', ['section' => 'qr']) : null,
                'verification' => $request->user() ? route('verification.notice') : null,
            ],
            'csrfToken' => csrf_token(),
        ];

        return view('public.tools.qr', compact('seo', 'campaigns', 'qrConfig'));
    }
}
