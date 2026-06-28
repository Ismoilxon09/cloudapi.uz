<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Telegram\BotNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class TelegramLoginController extends Controller
{
    /**
     * Login sahifasi
     */
    public function showForm()
    {
        return view('auth.telegram-login');
    }

    /**
     * Kod yuborish
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:50',
        ]);

        $identifier = trim($request->identifier);

        // IP throttle (5 ta/soat)
        $ipKey = 'tg_send_code_ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            return back()->withErrors(['identifier' => 'Juda ko\'p urinish. Bir soatdan keyin qayta urining.']);
        }

        // User topish
        $user = $this->findUser($identifier);

        if (!$user) {
            // Xavfsizlik: aniq xato bermaslik
            RateLimiter::hit($ipKey, 3600);
            return back()->withErrors([
                'identifier' => 'Foydalanuvchi topilmadi. Avval @' . env('TELEGRAM_BOT_USERNAME', 'cloudapiuzbot') . ' botiga /start bosing.',
            ]);
        }

        if (!$user->telegram_id) {
            return back()->withErrors(['identifier' => 'Bu akkaunt Telegram\'ga ulanmagan.']);
        }

        if ($user->status === 'blocked') {
            return back()->withErrors(['identifier' => 'Akkaunt bloklangan.']);
        }

        // Per-user throttle (1 daqiqada 1 kod)
        $userKey = 'tg_send_code_user:' . $user->id;
        if (RateLimiter::tooManyAttempts($userKey, 1)) {
            $seconds = RateLimiter::availableIn($userKey);
            return back()->withErrors(['identifier' => "Iltimos {$seconds} soniya kuting."]);
        }
        RateLimiter::hit($userKey, 60);
        RateLimiter::hit($ipKey, 3600);

        // Eski kodlarni bekor qilish
        DB::table('telegram_verification_codes')
            ->where('telegram_id', $user->telegram_id)
            ->where('purpose', 'login')
            ->where('used', false)
            ->update(['used' => true]);

        // 6 raqamli kod
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('telegram_verification_codes')->insert([
            'telegram_id' => $user->telegram_id,
            'code' => $code,
            'purpose' => 'login',
            'attempts' => 0,
            'used' => false,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now(),
        ]);

        // Bot orqali yuborish
        $notifier = app(BotNotifier::class);
        $sent = $notifier->sendLoginCode($user->telegram_id, $code);

        if (!$sent) {
            return back()->withErrors([
                'identifier' => 'Kod yuborib bo\'lmadi. Bot bilan suhbatni boshlaganingizga ishonch hosil qiling (/start).',
            ]);
        }

        // Session'ga user_id saqlash (verify uchun)
        session(['tg_login_user_id' => $user->id]);

        return redirect()->route('telegram.verify')
            ->with('success', 'Kod yuborildi. Botingizni tekshiring.');
    }

    /**
     * Kod tasdiqlash sahifasi
     */
    public function showVerifyForm()
    {
        if (!session('tg_login_user_id')) {
            return redirect()->route('telegram.login');
        }
        return view('auth.telegram-verify');
    }

    /**
     * Kod tekshirish
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6|regex:/^\d{6}$/',
        ]);

        $userId = session('tg_login_user_id');
        if (!$userId) {
            return redirect()->route('telegram.login');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('telegram.login');
        }

        // IP brute-force himoyasi
        $bfKey = 'tg_verify_bf:' . $request->ip() . ':' . $user->id;
        if (RateLimiter::tooManyAttempts($bfKey, 5)) {
            session()->forget('tg_login_user_id');
            return redirect()->route('telegram.login')->withErrors([
                'identifier' => 'Juda ko\'p noto\'g\'ri urinish. 15 daqiqadan keyin urining.',
            ]);
        }

        $verification = DB::table('telegram_verification_codes')
            ->where('telegram_id', $user->telegram_id)
            ->where('code', $request->code)
            ->where('purpose', 'login')
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            RateLimiter::hit($bfKey, 900);
            return back()->withErrors(['code' => 'Kod noto\'g\'ri yoki muddati o\'tgan']);
        }

        // Tasdiqlangan — login
        DB::table('telegram_verification_codes')
            ->where('id', $verification->id)
            ->update(['used' => true]);

        session()->forget('tg_login_user_id');
        RateLimiter::clear($bfKey);

        Auth::login($user, true);
        session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', '✅ Kirildi!');
    }

    /**
     * User topish (telefon yoki username)
     */
    protected function findUser(string $identifier): ?User
    {
        // @username
        if (str_starts_with($identifier, '@')) {
            $username = ltrim($identifier, '@');
            return User::where('telegram_username', $username)->first();
        }

        // Telefon raqam — faqat raqamlar
        $phone = preg_replace('/[^\d]/', '', $identifier);
        if (strlen($phone) >= 9) {
            // 998 prefiks qo'shish (agar yo'q bo'lsa)
            if (strlen($phone) === 9) {
                $phone = '998' . $phone;
            } elseif (strlen($phone) === 12 && !str_starts_with($phone, '998')) {
                // boshqa davlatlar
            }
            return User::where('phone', $phone)->first();
        }

        // username (@ siz)
        return User::where('telegram_username', $identifier)->first();
    }
}