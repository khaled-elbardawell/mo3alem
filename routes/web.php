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
use App\Http\Controllers\Site\SmartBoardToolController;
use App\Http\Controllers\Site\WheelController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SmartBoardAssetController;
use App\Http\Controllers\SmartBoardController;
use App\Http\Controllers\SmartBoardJoinController;
use App\Http\Controllers\SmartBoardPollResponseController;
use App\Http\Controllers\SmartBoardSessionController;
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
Route::get('/tools/smart-board', SmartBoardToolController::class)->name('tools.smart-board');
Route::get('/board/join/{smartBoardSession:public_code}', SmartBoardJoinController::class)
    ->middleware('throttle:smart-board-join')
    ->name('smart-board.join');
Route::post('/smart-board-sessions', [SmartBoardSessionController::class, 'store'])
    ->middleware('throttle:smart-board-session-creation')
    ->name('smart-board-sessions.store');
Route::get('/smart-board-sessions/{smartBoardSession:public_code}', [SmartBoardSessionController::class, 'show'])
    ->middleware('throttle:smart-board-polling')
    ->name('smart-board-sessions.show');
Route::patch('/smart-board-sessions/{smartBoardSession:public_code}', [SmartBoardSessionController::class, 'update'])
    ->middleware('throttle:smart-board-session-updates')
    ->name('smart-board-sessions.update');
Route::delete('/smart-board-sessions/{smartBoardSession:public_code}', [SmartBoardSessionController::class, 'destroy'])
    ->middleware('throttle:smart-board-session-updates')
    ->name('smart-board-sessions.destroy');
Route::post('/smart-board-sessions/{smartBoardSession:public_code}/responses', SmartBoardPollResponseController::class)
    ->middleware('throttle:smart-board-responses')
    ->name('smart-board-sessions.responses.store');
Route::get('/tools/certificates/auth/{action}', CertificateAuthRedirectController::class)
    ->whereIn('action', ['login', 'register'])
    ->name('tools.certificates.auth');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::post('/c/{adCampaign}/open', AdClickController::class)
    ->middleware('throttle:120,1')
    ->name('ads.click');
Route::post('/c/{adCampaign}/view', AdImpressionController::class)
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

Route::middleware(['auth', 'active', 'password.changed'])->group(function (): void {
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

    Route::post('/smart-boards/{smartBoard}/assets', [SmartBoardAssetController::class, 'store'])
        ->name('smart-boards.assets.store');
    Route::get('/smart-board-assets/{smartBoardAsset}', [SmartBoardAssetController::class, 'show'])
        ->name('smart-board-assets.show');
    Route::delete('/smart-board-assets/{smartBoardAsset}', [SmartBoardAssetController::class, 'destroy'])
        ->name('smart-board-assets.destroy');
    Route::post('/smart-boards', [SmartBoardController::class, 'store'])
        ->middleware('throttle:smart-board-creation')
        ->name('smart-boards.store');
    Route::resource('smart-boards', SmartBoardController::class)
        ->parameters(['smart-boards' => 'smartBoard'])
        ->only(['index', 'show', 'update', 'destroy']);
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'active', 'password.changed', 'admin'])
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

        Route::patch('/qr-templates/{qrTemplate}/restore', [Admin\QrTemplateController::class, 'restore'])
            ->withTrashed()
            ->name('qr-templates.restore');
        Route::resource('qr-templates', Admin\QrTemplateController::class)
            ->parameters(['qr-templates' => 'qrTemplate'])
            ->except(['show'])
            ->withTrashed();

        Route::patch('/certificate-templates/{certificateTemplate}/restore', [Admin\CertificateTemplateController::class, 'restore'])
            ->withTrashed()
            ->name('certificate-templates.restore');
        Route::resource('certificate-templates', Admin\CertificateTemplateController::class)
            ->parameters(['certificate-templates' => 'certificateTemplate'])
            ->except(['show'])
            ->withTrashed();

        Route::post('/api-clients/{apiClient}/token', [Admin\ApiClientController::class, 'rotateToken'])
            ->name('api-clients.token.rotate');
        Route::delete('/api-clients/{apiClient}/tokens', [Admin\ApiClientController::class, 'revokeTokens'])
            ->name('api-clients.tokens.revoke');
        Route::resource('api-clients', Admin\ApiClientController::class)
            ->parameters(['api-clients' => 'apiClient'])
            ->only(['index', 'create', 'store', 'edit', 'update']);

        Route::get('/analytics', Admin\AnalyticsController::class)->name('analytics');
        Route::get('/settings', [Admin\SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings/footer-links', [Admin\SiteSettingController::class, 'updateFooterLinks'])
            ->name('settings.footer-links.update');
        Route::get('/seo', [Admin\SeoSettingController::class, 'edit'])->name('seo.edit');
        Route::put('/seo/{page}', [Admin\SeoSettingController::class, 'update'])->name('seo.update');
        Route::get('/audit-logs', Admin\AuditLogController::class)->name('audit-logs');
    });
