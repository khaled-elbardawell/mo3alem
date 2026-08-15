<?php

namespace App\Http\Controllers\Site;

use App\AdPlacement;
use App\Http\Controllers\Controller;
use App\Http\Resources\QrCodeResource;
use App\Models\QrCode;
use App\Models\SeoSetting;
use App\Services\AdCampaignSelector;
use App\Services\MetricService;
use App\Services\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

use function Illuminate\Support\defer;

class QrToolController extends Controller
{
    public function __invoke(
        Request $request,
        AdCampaignSelector $ads,
        MetricService $metrics,
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
        $qrConfig = [
            'authenticated' => (bool) $request->user(),
            'verified' => $request->user()?->hasVerifiedEmail() ?? false,
            'limits' => [
                'savedQrCodes' => (int) config('resource_limits.qr_codes', 10),
            ],
            'usage' => [
                'savedQrCodes' => $request->user()?->qrCodes()->count() ?? 0,
            ],
            'savedQrCode' => $loadedQrCode
                ? QrCodeResource::make($loadedQrCode)->resolve($request)
                : null,
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
