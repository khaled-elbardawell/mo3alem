<?php

namespace App\Http\Controllers\Admin;

use App\AdCampaignStatus;
use App\AdPlacement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdCampaignStoreRequest;
use App\Http\Requests\Admin\AdCampaignUpdateRequest;
use App\Models\AdCampaign;
use App\Services\AdminAuditService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdCampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = AdCampaign::withTrashed()
            ->withSum('dailyStats as impressions', 'impressions')
            ->withSum('dailyStats as clicks', 'clicks')
            ->latest()
            ->paginate(20);

        return view('admin.ad-campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        return view('admin.ad-campaigns.form', [
            'campaign' => new AdCampaign,
            'placements' => AdPlacement::cases(),
            'statuses' => AdCampaignStatus::cases(),
        ]);
    }

    public function store(
        AdCampaignStoreRequest $request,
        AdminAuditService $audit,
    ): RedirectResponse {
        $data = $request->safe()->except('image');
        $data = $this->utcDates($data);
        $data['image_path'] = $request->file('image')->store('ads', 'public');
        $campaign = AdCampaign::query()->create($data);
        $audit->record($request, 'ad-campaign.created', $campaign, null, $campaign->toArray());

        return redirect()->route('admin.ad-campaigns.index')->with('status', 'تم إنشاء الحملة.');
    }

    public function edit(AdCampaign $adCampaign): View
    {
        return view('admin.ad-campaigns.form', [
            'campaign' => $adCampaign,
            'placements' => AdPlacement::cases(),
            'statuses' => AdCampaignStatus::cases(),
        ]);
    }

    public function update(
        AdCampaignUpdateRequest $request,
        AdCampaign $adCampaign,
        AdminAuditService $audit,
    ): RedirectResponse {
        $before = $adCampaign->toArray();
        $data = $request->safe()->except('image');
        $data = $this->utcDates($data);
        $oldImage = null;

        if ($request->hasFile('image')) {
            $oldImage = $adCampaign->image_path;
            $data['image_path'] = $request->file('image')->store('ads', 'public');
        }

        $adCampaign->update($data);

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        $audit->record($request, 'ad-campaign.updated', $adCampaign, $before, $adCampaign->fresh()->toArray());

        return redirect()->route('admin.ad-campaigns.index')->with('status', 'تم تحديث الحملة.');
    }

    public function destroy(
        Request $request,
        AdCampaign $adCampaign,
        AdminAuditService $audit,
    ): RedirectResponse {
        $before = $adCampaign->toArray();
        $adCampaign->delete();
        $audit->record($request, 'ad-campaign.deleted', $adCampaign, $before);

        return back()->with('status', 'تم حذف الحملة.');
    }

    public function restore(
        Request $request,
        AdCampaign $adCampaign,
        AdminAuditService $audit,
    ): RedirectResponse {
        $adCampaign->restore();
        $audit->record($request, 'ad-campaign.restored', $adCampaign, null, $adCampaign->fresh()->toArray());

        return back()->with('status', 'تمت استعادة الحملة.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function utcDates(array $data): array
    {
        foreach (['starts_at', 'ends_at'] as $key) {
            if (! empty($data[$key])) {
                $data[$key] = CarbonImmutable::parse($data[$key], config('app.timezone'))->utc();
            }
        }

        return $data;
    }
}
