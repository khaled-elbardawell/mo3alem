<?php

use App\Http\Controllers\ActivityMetricController;
use App\Http\Controllers\AdClickController;
use App\Http\Controllers\AdImpressionController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\SocialAuthenticationController;
use App\Http\Controllers\CertificateAuthRedirectController;
use App\Http\Controllers\CertificateBackgroundController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QrAuthRedirectController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\QrLogoController;
use App\Http\Controllers\QrPreviewController;
use App\Http\Controllers\QrRedirectController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SavedWheelController;
use App\Http\Controllers\Site\CertificateToolController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\QrToolController;
use App\Http\Controllers\Site\WheelController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\User\CompetitionController as UserCompetitionController;
use App\Http\Controllers\User\CompetitionNameController;
use App\Http\Controllers\User\SavedWheelController as UserSavedWheelController;
use App\Http\Controllers\User\SavedWheelNameController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/tools/wheel', WheelController::class)->name('tools.wheel');
Route::get('/q/{qrCode:public_code}', QrRedirectController::class)
    ->withTrashed()
    ->middleware('throttle:qr-redirect')
    ->name('qr.redirect');
Route::get('/tools/qr', QrToolController::class)->name('tools.qr');
Route::post('/tools/qr/render', QrPreviewController::class)
    ->middleware('throttle:120,1')
    ->name('tools.qr.render');
Route::get('/tools/qr/auth/{action}', QrAuthRedirectController::class)
    ->whereIn('action', ['login', 'register'])
    ->name('tools.qr.auth');
Route::get('/tools/certificates', CertificateToolController::class)->name('tools.certificates');
Route::get('/tools/certificates/auth/{action}', CertificateAuthRedirectController::class)
    ->whereIn('action', ['login', 'register'])
    ->name('tools.certificates.auth');
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

Route::middleware(['guest', 'throttle:social-authentication'])
    ->prefix('auth/{provider}')
    ->whereIn('provider', ['google'])
    ->group(function (): void {
        Route::get('/redirect', [SocialAuthenticationController::class, 'redirect'])
            ->name('social.redirect');
        Route::get('/callback', [SocialAuthenticationController::class, 'callback'])
            ->name('social.callback');
    });

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

    Route::post('/qr-codes', [QrCodeController::class, 'store'])
        ->middleware('throttle:qr-code-creation')
        ->name('qr-codes.store');
    Route::get('/qr-codes/{qrCode}/logo', QrLogoController::class)->name('qr-codes.logo');
    Route::resource('qr-codes', QrCodeController::class)
        ->parameters(['qr-codes' => 'qrCode'])
        ->only(['index', 'show', 'update', 'destroy']);

    Route::post('/certificates', [CertificateController::class, 'store'])
        ->middleware('throttle:certificate-creation')
        ->name('certificates.store');
    Route::get('/certificates/{certificate}/background', CertificateBackgroundController::class)
        ->name('certificates.background');
    Route::resource('certificates', CertificateController::class)
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
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
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
