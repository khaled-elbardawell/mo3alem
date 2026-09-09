<?php

namespace App\Console\Commands;

use App\Models\AdCampaign;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

#[Signature('campaign-media:migrate')]
#[Description('Copy legacy campaign images to the neutral media directory and update their paths')]
class MigrateCampaignMedia extends Command
{
    public function handle(): int
    {
        $disk = Storage::disk('public');
        $migrated = 0;
        $failed = 0;

        AdCampaign::withTrashed()
            ->where('image_path', 'like', 'ads/%')
            ->lazyById()
            ->each(function (AdCampaign $campaign) use ($disk, &$migrated, &$failed): void {
                $sourcePath = $campaign->image_path;
                $destinationPath = Str::of($sourcePath)
                    ->after('ads/')
                    ->prepend('campaign-media/')
                    ->toString();

                try {
                    if (! $disk->exists($sourcePath)) {
                        $this->error("Missing source file for campaign {$campaign->id}: {$sourcePath}");
                        $failed++;

                        return;
                    }

                    if (! $disk->exists($destinationPath) && ! $disk->copy($sourcePath, $destinationPath)) {
                        $this->error("Could not copy media for campaign {$campaign->id}.");
                        $failed++;

                        return;
                    }

                    $campaign->forceFill(['image_path' => $destinationPath])->saveQuietly();
                    $migrated++;
                } catch (Throwable $exception) {
                    $this->error("Could not migrate campaign {$campaign->id}: {$exception->getMessage()}");
                    $failed++;
                }
            });

        $this->info("Migrated {$migrated} campaign media file(s); {$failed} failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
