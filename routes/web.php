<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\BillingController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\KeyController;
use App\Http\Controllers\Dashboard\TicketController;
use App\Http\Controllers\Admin\FeedbackController;
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

// Agent botlari webhook (foydalanuvchi agentlari, per-agent secret)
Route::post('/api/agent/webhook/{secret}', [\App\Http\Controllers\Bot\AgentBotWebhookController::class, 'handle'])
    ->name('agent.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Agent public API (server-to-server, kalit bilan) + web widget
Route::post('/api/agent/{slug}/chat', [\App\Http\Controllers\Api\AgentApiController::class, 'chat'])
    ->name('agent.api.chat')
    ->middleware('throttle:60,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::get('/agent/{slug}/widget.js', [\App\Http\Controllers\Api\AgentApiController::class, 'widgetJs'])
    ->name('agent.widget.js');
Route::post('/api/agent/{slug}/widget', [\App\Http\Controllers\Api\AgentApiController::class, 'widgetMessage'])
    ->name('agent.widget.message')
    ->middleware('throttle:40,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::options('/api/agent/{slug}/widget', [\App\Http\Controllers\Api\AgentApiController::class, 'widgetPreflight'])
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
    
    // Tickets
    Route::prefix('dashboard/tickets')->name('dashboard.tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/create', [TicketController::class, 'create'])->name('create');
        Route::post('/', [TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
    });

    // Media kutubxonasi (yaratilgan rasm/video/audio)
    Route::get('/dashboard/media', [\App\Http\Controllers\Dashboard\MediaController::class, 'index'])->name('media.index');

    // AI Agentlar (builder) — minimal balans talab qilinadi
    Route::prefix('dashboard/agents')->name('agents.')
        ->middleware(\App\Http\Middleware\RequireAgentAccess::class)
        ->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\AgentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Dashboard\AgentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Dashboard\AgentController::class, 'store'])->name('store');
        Route::get('/{agent}/edit', [\App\Http\Controllers\Dashboard\AgentController::class, 'edit'])->name('edit');
        Route::put('/{agent}', [\App\Http\Controllers\Dashboard\AgentController::class, 'update'])->name('update');
        Route::delete('/{agent}', [\App\Http\Controllers\Dashboard\AgentController::class, 'destroy'])->name('destroy');
        Route::post('/{agent}/toggle', [\App\Http\Controllers\Dashboard\AgentController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{agent}/telegram', [\App\Http\Controllers\Dashboard\AgentController::class, 'connectTelegram'])->name('telegram.connect');
        Route::delete('/{agent}/telegram', [\App\Http\Controllers\Dashboard\AgentController::class, 'disconnectTelegram'])->name('telegram.disconnect');
        Route::get('/{agent}/telegram/status', [\App\Http\Controllers\Dashboard\AgentController::class, 'telegramStatus'])->name('telegram.status');
        Route::post('/{agent}/telegram/reset', [\App\Http\Controllers\Dashboard\AgentController::class, 'resetWebhook'])->name('telegram.reset');
        Route::post('/{agent}/test', [\App\Http\Controllers\Dashboard\AgentController::class, 'testAgent'])->name('test');
        // MCP serverlar
        Route::post('/{agent}/mcp', [\App\Http\Controllers\Dashboard\AgentController::class, 'addMcp'])->name('mcp.add');
        Route::post('/{agent}/mcp/{mcp}/test', [\App\Http\Controllers\Dashboard\AgentController::class, 'testMcp'])->name('mcp.test');
        Route::post('/{agent}/mcp/{mcp}/toggle', [\App\Http\Controllers\Dashboard\AgentController::class, 'toggleMcp'])->name('mcp.toggle');
        Route::delete('/{agent}/mcp/{mcp}', [\App\Http\Controllers\Dashboard\AgentController::class, 'deleteMcp'])->name('mcp.delete');
        // API kanal + web widget
        Route::post('/{agent}/api-key', [\App\Http\Controllers\Dashboard\AgentController::class, 'generateApiKey'])->name('api.key');
        Route::delete('/{agent}/api-key', [\App\Http\Controllers\Dashboard\AgentController::class, 'revokeApi'])->name('api.revoke');
        Route::post('/{agent}/widget', [\App\Http\Controllers\Dashboard\AgentController::class, 'saveWidget'])->name('widget.save');
        Route::delete('/{agent}/widget', [\App\Http\Controllers\Dashboard\AgentController::class, 'disableWidget'])->name('widget.disable');
    });

    // Vantage — observability hub (minimal balans talab qilinadi)
    Route::middleware(\App\Http\Middleware\RequireAgentAccess::class)->group(function () {
        Route::get('/dashboard/vantage', [\App\Http\Controllers\Dashboard\VantageController::class, 'index'])->name('vantage.index');
        Route::get('/dashboard/vantage/town', [\App\Http\Controllers\Dashboard\VantageController::class, 'town'])->name('vantage.town');
        Route::get('/dashboard/vantage/stream', [\App\Http\Controllers\Dashboard\VantageController::class, 'stream'])->name('vantage.stream');
    });

    // Chat
    Route::prefix('dashboard/chat')->name('dashboard.chat.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\ChatController::class, 'index'])->name('index');
        Route::get('/{sessionId}', [\App\Http\Controllers\Dashboard\ChatController::class, 'show'])->name('show')->whereNumber('sessionId');
        Route::post('/create', [\App\Http\Controllers\Dashboard\ChatController::class, 'create'])->name('create');
        Route::post('/send', [\App\Http\Controllers\Dashboard\ChatController::class, 'sendMessage'])->name('send');
        Route::delete('/{sessionId}', [\App\Http\Controllers\Dashboard\ChatController::class, 'destroy'])->name('destroy');
        Route::post('/{sessionId}/pin', [\App\Http\Controllers\Dashboard\ChatController::class, 'togglePin'])->name('pin');
        Route::put('/{sessionId}/title', [\App\Http\Controllers\Dashboard\ChatController::class, 'updateTitle'])->name('title');
        Route::post('/stream', [\App\Http\Controllers\Dashboard\ChatController::class, 'streamMessage'])->name('stream');
        Route::post('/{sessionId}/regenerate', [\App\Http\Controllers\Dashboard\ChatController::class, 'regenerate'])->name('regenerate')->whereNumber('sessionId');
        Route::post('/{sessionId}/edit', [\App\Http\Controllers\Dashboard\ChatController::class, 'editMessage'])->name('edit')->whereNumber('sessionId');
        Route::put('/{sessionId}/settings', [\App\Http\Controllers\Dashboard\ChatController::class, 'updateSettings'])->name('settings')->whereNumber('sessionId');
     });
});

// ==========================================
// ADMIN PANEL ROUTES
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // AI Chat — barcha foydalanuvchilar chatlarini nazorat (read-only)
    Route::get('/chat', [\App\Http\Controllers\Admin\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{sessionId}', [\App\Http\Controllers\Admin\ChatController::class, 'show'])->name('chat.show')->whereNumber('sessionId');

    // AI Agentlar (nazorat & boshqaruv)
    Route::get('/agents', [\App\Http\Controllers\Admin\AgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/{agent}', [\App\Http\Controllers\Admin\AgentController::class, 'show'])->name('agents.show')->whereNumber('agent');
    Route::post('/agents/{agent}/toggle', [\App\Http\Controllers\Admin\AgentController::class, 'toggleStatus'])->name('agents.toggle');
    Route::delete('/agents/{agent}', [\App\Http\Controllers\Admin\AgentController::class, 'destroy'])->name('agents.destroy');

    // Vantage — platforma bo'yicha jonli kuzatuv
    Route::get('/vantage', [\App\Http\Controllers\Admin\VantageController::class, 'index'])->name('vantage.index');
    Route::get('/vantage/stream', [\App\Http\Controllers\Admin\VantageController::class, 'stream'])->name('vantage.stream');

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

    // Admin tickets
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('index');
        Route::get('/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [\App\Http\Controllers\Admin\TicketController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/close', [\App\Http\Controllers\Admin\TicketController::class, 'close'])->name('close');
        Route::post('/{ticket}/status', [\App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('status');
        Route::post('/{ticket}/priority', [\App\Http\Controllers\Admin\TicketController::class, 'updatePriority'])->name('priority');
        Route::delete('/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'destroy'])->name('destroy');
    });

    // Admin feedbacks
    Route::prefix('feedbacks')->name('feedbacks.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\FeedbackController::class, 'index'])->name('index');
        Route::get('/{feedback}', [\App\Http\Controllers\Admin\FeedbackController::class, 'show'])->name('show');
        Route::post('/{feedback}/reply', [\App\Http\Controllers\Admin\FeedbackController::class, 'reply'])->name('reply');
        Route::post('/{feedback}/toggle-publish', [\App\Http\Controllers\Admin\FeedbackController::class, 'togglePublish'])->name('toggle-publish');
        Route::post('/{feedback}/toggle-feature', [\App\Http\Controllers\Admin\FeedbackController::class, 'toggleFeature'])->name('toggle-feature');
        Route::delete('/{feedback}', [\App\Http\Controllers\Admin\FeedbackController::class, 'destroy'])->name('destroy');
    });
});
Route::post('/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');