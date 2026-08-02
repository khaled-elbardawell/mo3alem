<?php

use App\Http\Controllers\ActivityMetricController;
use App\Http\Controllers\AdClickController;
use App\Http\Controllers\AdImpressionController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SavedWheelController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\WheelController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\User\CompetitionController as UserCompetitionController;
use App\Http\Controllers\User\CompetitionNameController;
use App\Http\Controllers\User\SavedWheelController as UserSavedWheelController;
use App\Http\Controllers\User\SavedWheelNameController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/tools/wheel', WheelController::class)->name('tools.wheel');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/ads/{adCampaign}/click', AdClickController::class)
    ->middleware('throttle:120,1')
    ->name('ads.click');
Route::post('/ads/{adCampaign}/impression', AdImpressionController::class)
    ->middleware('throttle:120,1')
    ->name('ads.impression');
Route::post('/activity-metrics', ActivityMetricController::class)
    ->middleware('throttle:240,1')
    ->name('activity-metrics.store');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/competitions/{competition}', [UserCompetitionController::class, 'show'])
        ->name('user.competitions.show');
    Route::post('/dashboard/competitions/{competition}/names', [CompetitionNameController::class, 'store'])
        ->middleware('throttle:120,1')
        ->name('user.competitions.names.store');
    Route::delete('/dashboard/competitions/{competition}/names/{nameIndex}', [CompetitionNameController::class, 'destroy'])
        ->whereNumber('nameIndex')
        ->middleware('throttle:120,1')
        ->name('user.competitions.names.destroy');
    Route::get('/dashboard/lists/{savedWheel}', [UserSavedWheelController::class, 'show'])
        ->name('user.saved-wheels.show');
    Route::post('/dashboard/lists/{savedWheel}/names', [SavedWheelNameController::class, 'store'])
        ->middleware('throttle:120,1')
        ->name('user.saved-wheels.names.store');
    Route::delete('/dashboard/lists/{savedWheel}/names/{nameIndex}', [SavedWheelNameController::class, 'destroy'])
        ->whereNumber('nameIndex')
        ->middleware('throttle:120,1')
        ->name('user.saved-wheels.names.destroy');
    Route::view('/profile', 'users.profile.edit')->name('profile.edit');

    Route::post('/saved-wheels', [SavedWheelController::class, 'store'])
        ->middleware('throttle:saved-wheel-creation')
        ->name('saved-wheels.store');
    Route::resource('saved-wheels', SavedWheelController::class)
        ->parameters(['saved-wheels' => 'savedWheel'])
        ->only(['index', 'show', 'update', 'destroy']);

    Route::post('/competitions', [CompetitionController::class, 'store'])
        ->middleware('throttle:competition-creation')
        ->name('competitions.store');
    Route::resource('competitions', CompetitionController::class)
        ->only(['index', 'show', 'update', 'destroy']);
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'active', 'admin'])
    ->group(function (): void {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::patch('/users/{user}/restore', [Admin\UserController::class, 'restore'])
            ->withTrashed()
            ->name('users.restore');
        Route::resource('users', Admin\UserController::class)
            ->only(['index', 'edit', 'update', 'destroy'])
            ->withTrashed();

        Route::patch('/saved-wheels/{savedWheel}/restore', [Admin\SavedWheelController::class, 'restore'])
            ->withTrashed()
            ->name('saved-wheels.restore');
        Route::resource('saved-wheels', Admin\SavedWheelController::class)
            ->parameters(['saved-wheels' => 'savedWheel'])
            ->only(['index', 'edit', 'update', 'destroy'])
            ->withTrashed();

        Route::patch('/ad-campaigns/{adCampaign}/restore', [Admin\AdCampaignController::class, 'restore'])
            ->withTrashed()
            ->name('ad-campaigns.restore');
        Route::resource('ad-campaigns', Admin\AdCampaignController::class)
            ->parameters(['ad-campaigns' => 'adCampaign'])
            ->except(['show'])
            ->withTrashed();

        Route::get('/analytics', Admin\AnalyticsController::class)->name('analytics');
        Route::get('/seo', [Admin\SeoSettingController::class, 'edit'])->name('seo.edit');
        Route::put('/seo', [Admin\SeoSettingController::class, 'update'])->name('seo.update');
        Route::get('/audit-logs', Admin\AuditLogController::class)->name('audit-logs');
    });
