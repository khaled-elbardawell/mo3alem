<?php

namespace App\Services;

use App\AdPlacement;
use App\Models\AdCampaign;
use Illuminate\Database\Eloquent\Collection;

class AdCampaignSelector
{
    public function select(AdPlacement $placement): ?AdCampaign
    {
        $campaigns = AdCampaign::query()
            ->eligible($placement)
            ->select(['id', 'title', 'image_path', 'target_url', 'alt_text', 'placement', 'weight'])
            ->get();

        return $this->weighted($campaigns);
    }

    /**
     * @param  Collection<int, AdCampaign>  $campaigns
     */
    private function weighted(Collection $campaigns): ?AdCampaign
    {
        $totalWeight = $campaigns->sum(fn (AdCampaign $campaign): int => max(1, $campaign->weight));

        if ($totalWeight === 0) {
            return null;
        }

        $target = random_int(1, $totalWeight);

        foreach ($campaigns as $campaign) {
            $target -= max(1, $campaign->weight);

            if ($target <= 0) {
                return $campaign;
            }
        }

        return $campaigns->last();
    }
}
