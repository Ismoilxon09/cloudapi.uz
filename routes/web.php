<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\BillingController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\KeyController;
use App\Http\Controllers\Dashboard\PlaygroundController;
use App\Http\Controllers\ModelsController;
use App\Http\Controllers\PromoCodeController;
use Illuminate\Support\Facades\Route;

// === LANDING ===
Route::get('/', fn() => view('landing.home'))->name('home');
Route::get('/pricing', fn() => view('landing.pricing'))->name('pricing');
Route::get('/docs', fn() => view('landing.docs'))->name('docs');
Route::get('/privacy', fn() => view('landing.privacy'))->name('privacy');
Route::get('/security', fn() => view('landing.security'))->name('security');
Route::get('/terms', fn() => view('landing.terms'))->name('terms');

// === MODELS (public browse) ===
Route::get('/models', [ModelsController::class, 'index'])->name('models.index');
Route::get('/models/{modelId}', [ModelsController::class, 'show'])
    ->where('modelId', '.*')
    ->name('models.show');

// === AUTH ===
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // OAuth
    Route::get('/auth/{provider}', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])
        ->name('oauth.redirect')
        ->where('provider', 'google|github');
    Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])
        ->where('provider', 'google|github');

    // Password reset
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update');

    // Telegram login
    Route::get('/telegram-login', [\App\Http\Controllers\Auth\TelegramLoginController::class, 'showForm'])->name('telegram.login');
    Route::post('/telegram-login', [\App\Http\Controllers\Auth\TelegramLoginController::class, 'sendCode'])->name('telegram.send-code');
    Route::get('/telegram-verify', [\App\Http\Controllers\Auth\TelegramLoginController::class, 'showVerifyForm'])->name('telegram.verify');
    Route::post('/telegram-verify', [\App\Http\Controllers\Auth\TelegramLoginController::class, 'verify']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Bot webhook (CSRF dan tashqari)
Route::post('/api/bot/webhook/{secret}', [\App\Http\Controllers\Bot\BotWebhookController::class, 'handle'])
    ->name('bot.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Email verification (logged in users)
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'show'])->name('verification.notice');
    Route::post('/verify-email/send', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'send'])->name('verification.send');
    Route::get('/verify-email/{token}', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])->name('verification.verify');
});

// === AUTHENTICATED ===
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // API Keys (explicit routes — no resource)
    Route::get('/keys', [KeyController::class, 'index'])->name('keys.index');
    Route::post('/keys', [KeyController::class, 'store'])->name('keys.store');
    Route::get('/keys/{key}', [KeyController::class, 'show'])->name('keys.show');
    Route::delete('/keys/{key}', [KeyController::class, 'destroy'])->name('keys.destroy');
    Route::post('/keys/{key}/revoke', [KeyController::class, 'revoke'])->name('keys.revoke');

    // Playground
    Route::get('/playground', [PlaygroundController::class, 'index'])->name('playground.index');
    Route::post('/playground/run', [PlaygroundController::class, 'run'])->name('playground.run');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/topup', [BillingController::class, 'showTopup'])->name('billing.topup');
    Route::post('/billing/topup', [BillingController::class, 'topup'])->name('billing.topup.submit');

    // Notifications
    Route::get('/dashboard/notifications', [\App\Http\Controllers\Dashboard\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/dashboard/notifications/dropdown', [\App\Http\Controllers\Dashboard\NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::post('/dashboard/notifications/mark-all-read', [\App\Http\Controllers\Dashboard\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('/dashboard/notifications/{id}/read', [\App\Http\Controllers\Dashboard\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/dashboard/notifications/{id}', [\App\Http\Controllers\Dashboard\NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Activity (usage history & analytics)
    Route::get('/activity', [\App\Http\Controllers\Dashboard\ActivityController::class, 'index'])->name('activity.index');

    // Logs (API request logs)
    Route::get('/logs', [\App\Http\Controllers\Dashboard\LogsController::class, 'index'])->name('logs.index');

    // Settings (preferences)
    Route::get('/settings', [\App\Http\Controllers\Dashboard\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [\App\Http\Controllers\Dashboard\SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [\App\Http\Controllers\Dashboard\SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/promo/redeem', [\App\Http\Controllers\PromoCodeController::class, 'redeem'])
        ->name('promo.redeem');
    Route::get('/promo/my-uses', [\App\Http\Controllers\PromoCodeController::class, 'myUses'])
        ->name('promo.my-uses');
});

// ==========================================
// ADMIN PANEL ROUTES
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/balance', [\App\Http\Controllers\Admin\UserController::class, 'adjustBalance'])->name('users.balance');
    Route::post('/users/{user}/block', [\App\Http\Controllers\Admin\UserController::class, 'block'])->name('users.block');
    Route::post('/users/{user}/unblock', [\App\Http\Controllers\Admin\UserController::class, 'unblock'])->name('users.unblock');

    // Payments (manual topups)
    Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{tx}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{tx}/approve', [\App\Http\Controllers\Admin\PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{tx}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'reject'])->name('payments.reject');

    // Transactions (all)
    Route::get('/transactions', [\App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');

    // API Keys (global view)
    Route::get('/keys', [\App\Http\Controllers\Admin\KeyController::class, 'index'])->name('keys.index');

    // API Logs (global)
    Route::get('/logs', [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');

    // Models management
    Route::get('/models', [\App\Http\Controllers\Admin\ModelController::class, 'index'])->name('models.index');
    Route::post('/models/sync', [\App\Http\Controllers\Admin\ModelController::class, 'sync'])->name('models.sync');
    Route::post('/models/sync/groq', [\App\Http\Controllers\Admin\ModelController::class, 'syncGroq'])->name('models.sync.groq');
    Route::post('/models/{model}/toggle', [\App\Http\Controllers\Admin\ModelController::class, 'toggle'])->name('models.toggle');
    Route::post('/models/{model}/feature', [\App\Http\Controllers\Admin\ModelController::class, 'feature'])->name('models.feature');
    Route::post('/models/{model}/margin', [\App\Http\Controllers\Admin\ModelController::class, 'updateMargin'])->name('models.margin');

    // Statistics
    Route::get('/stats', [\App\Http\Controllers\Admin\StatsController::class, 'index'])->name('stats.index');
    Route::get('/stats/revenue', [\App\Http\Controllers\Admin\StatsController::class, 'revenue'])->name('stats.revenue');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [\App\Http\Controllers\Admin\NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{notif}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // Broadcasts
    Route::get('/broadcasts', [\App\Http\Controllers\Admin\BroadcastController::class, 'index'])->name('broadcasts.index');
    Route::get('/broadcasts/create', [\App\Http\Controllers\Admin\BroadcastController::class, 'create'])->name('broadcasts.create');
    Route::post('/broadcasts', [\App\Http\Controllers\Admin\BroadcastController::class, 'store'])->name('broadcasts.store');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');

    // Activity log (admin actions)
    Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');

});
Route::post('/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');