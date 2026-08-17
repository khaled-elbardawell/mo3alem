<?php

namespace App\Http\Controllers\Site;

use App\AdPlacement;
use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use App\Models\SeoSetting;
use App\Services\AdCampaignSelector;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

use function Illuminate\Support\defer;

class CertificateToolController extends Controller
{
    public function __invoke(
        Request $request,
        AdCampaignSelector $ads,
        MetricService $metrics,
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

        $seoValues = Cache::remember('seo:home', 300, function (): array {
            $seo = SeoSetting::query()->first() ?? new SeoSetting;

            return $seo->getAttributes();
        });
        $seo = (new SeoSetting)->forceFill($seoValues);
        $campaigns = [
            'top' => $ads->select(AdPlacement::Top),
            'side' => $ads->select(AdPlacement::Side),
            'bottom' => $ads->select(AdPlacement::Bottom),
        ];
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
            'templates' => $this->templates(),
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

    /** @return list<array{key: string, label: string, url: string, width: int, height: int}> */
    private function templates(): array
    {
        $dimensions = [
            'b1' => [1123, 794, 'jpg'],
            'b2' => [1123, 794, 'jpg'],
            'b3' => [1123, 794, 'jpg'],
            'b4' => [1123, 794, 'jpg'],
            'b5' => [1123, 794, 'jpg'],
            'b6' => [1123, 794, 'jpg'],
            'b7' => [1123, 794, 'jpg'],
            'b8' => [1123, 794, 'jpg'],
            'b9' => [1123, 794, 'jpg'],
            'b10' => [1123, 794, 'jpg'],
        ];

        return collect($dimensions)->map(function (array $template, string $key): array {
            [$width, $height, $extension] = $template;

            return [
                'key' => $key,
                'label' => 'قالب '.mb_substr($key, 1),
                'url' => asset("assets/certificate-templates/{$key}.{$extension}"),
                'width' => $width,
                'height' => $height,
            ];
        })->values()->all();
    }
}
