<?php

namespace App\Models;

use Database\Factories\DailyMetricFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'site_visits', 'registrations', 'active_users', 'saved_wheels', 'names_saved', 'spins', 'imports', 'qr_generated', 'qr_saved', 'certificate_generated', 'certificate_saved', 'ad_impressions', 'ad_clicks'])]
class DailyMetric extends Model
{
    /** @use HasFactory<DailyMetricFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
