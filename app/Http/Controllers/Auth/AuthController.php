<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Services\FraudDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private FraudDetectionService $fraud) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255',
            'remember' => 'nullable|boolean',
        ]);

        $throttleKey = 'login_attempts:' . $request->ip() . '|' . strtolower($validated['email']);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::channel('security')->warning("Login brute-force: {$validated['email']} from {$request->ip()}");
            return back()->withErrors([
                'email' => "Juda ko'p urinish. {$seconds} soniyadan keyin qayta urinib ko'ring.",
            ])->onlyInput('email');
        }

        $credentials = [
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
        ];

        if (Auth::attempt($credentials, $validated['remember'] ?? false)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (in_array($user->status, ['banned', 'blocked', 'suspended'])) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akkaunt bloklangan. Yordam uchun support\'ga murojaat qiling.']);
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_login_user_agent' => substr($request->userAgent() ?? '', 0, 500),
            ]);

            RateLimiter::clear($throttleKey);

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 900);

        return back()->withErrors([
            'email' => __('auth.login.failed'),
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (\App\Models\SystemSetting::get('registration_enabled') == false) {
            abort(503, 'Registration is temporarily disabled');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (\App\Models\SystemSetting::get('registration_enabled') == false) {
            return back()->withErrors(['email' => "Ro'yxatdan o'tish vaqtincha o'chirilgan"]);
        }

        $throttleKey = 'register_attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return back()->withErrors([
                'email' => "Juda ko'p urinish. Birozdan keyin qayta urinib ko'ring.",
            ]);
        }
        RateLimiter::hit($throttleKey, 3600);

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100|regex:/^[\p{L}\s\.\x27\-]+$/u',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255|confirmed',
            'referral_code' => 'nullable|string|max:12|regex:/^[A-Z0-9]+$/',
            'device_hash' => 'nullable|string|max:64',
        ]);

        if (!$this->isStrongPassword($validated['password'])) {
            return back()->withErrors([
                'password' => "Parol kuchli emas. Katta harf, kichik harf va raqam bo'lishi kerak.",
            ])->onlyInput('name', 'email');
        }

        $ip = $request->ip();
        $deviceHash = $validated['device_hash'] ?? null;
        $userAgent = substr($request->userAgent() ?? '', 0, 500);
        $signupBonus = (float)\App\Models\SystemSetting::get('signup_bonus_uzs', 0);

        // FRAUD CHECK
        $check = $this->fraud->check($ip, $validated['email'], $deviceHash, $userAgent, (int)$signupBonus);

        // Blok bo'lsa
        if ($check['blocked']) {
            $this->fraud->logAttempt([
                'ip_address' => $ip,
                'device_hash' => $deviceHash,
                'user_agent' => $userAgent,
                'email' => $validated['email'],
                'oauth_provider' => 'email',
                'success' => false,
                'fraud_score' => $check['fraud_score'],
                'blocked_reason' => $check['reason_internal'],
                'country' => $check['country'],
                'is_vpn' => $check['is_vpn'],
            ]);

            $this->notifyAdmin('🚫 Blocked signup attempt', [
                'ip' => $ip,
                'email' => $validated['email'],
                'reason' => $check['reason_internal'],
            ]);

            return back()->withErrors([
                'email' => $check['reason'],
            ])->onlyInput('name', 'email');
        }

        $actualBonus = $check['bonus_eligible'] ? (int)$signupBonus : 0;
        $user = null;
        $bonusMessage = null;

        DB::transaction(function () use ($validated, $request, $ip, $deviceHash, $userAgent, $actualBonus, $check, &$user, &$bonusMessage) {
            $referredBy = null;
            if (!empty($validated['referral_code'])) {
                $referredBy = User::where('referral_code', $validated['referral_code'])->first()?->id;
            }

            $user = User::create([
                'name' => strip_tags($validated['name']),
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'referral_code' => $this->generateReferralCode(),
                'referred_by' => $referredBy,
                'role' => 'user',
                'status' => 'active',
                'language' => app()->getLocale(),
                'signup_ip' => $ip,
                'signup_device_hash' => $deviceHash,
                'signup_user_agent' => $userAgent,
                'signup_country' => $check['country'],
                'is_suspicious' => $check['is_suspicious'] ? 1 : 0,
                'fraud_score' => $check['fraud_score'],
                'created_at' => now(),
            ]);

            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance_uzs' => 0,
                'bonus_balance_uzs' => $actualBonus,
                'total_deposited' => 0,
                'total_spent' => 0,
            ]);

            if ($actualBonus > 0) {
                $user->transactions()->create([
                    'wallet_id' => $wallet->id,
                    'type' => 'bonus',
                    'status' => 'completed',
                    'amount_uzs' => $actualBonus,
                    'balance_after' => $actualBonus,
                    'description' => "Ro'yxatdan o'tish bonusi",
                ]);
                $bonusMessage = "🎁 Tabriklaymiz! Sizga {$actualBonus} GP bonus berildi.";
            } else if ($check['reason']) {
                $bonusMessage = "ℹ️ " . $check['reason'];
            }

            // Referral bonusi (faqat suspicious bo'lmasa)
            if ($referredBy && !$check['is_suspicious']) {
                $referralBonus = (float)\App\Models\SystemSetting::get('referral_bonus_uzs', 0);
                if ($referralBonus > 0) {
                    $referrer = User::find($referredBy);
                    if ($referrer && $referrer->wallet) {
                        $referrer->wallet->increment('bonus_balance_uzs', $referralBonus);
                        $referrer->transactions()->create([
                            'wallet_id' => $referrer->wallet->id,
                            'type' => 'bonus',
                            'status' => 'completed',
                            'amount_uzs' => $referralBonus,
                            'description' => "Referral bonus: {$user->name}",
                        ]);
                    }
                }
            }

            $this->fraud->logAttempt([
                'ip_address' => $ip,
                'device_hash' => $deviceHash,
                'user_agent' => $userAgent,
                'email' => $validated['email'],
                'oauth_provider' => 'email',
                'success' => true,
                'user_id' => $user->id,
                'fraud_score' => $check['fraud_score'],
                'blocked_reason' => $check['reason_internal'],
                'country' => $check['country'],
                'is_vpn' => $check['is_vpn'],
            ]);

            Auth::login($user);
            $request->session()->regenerate();
        });

        if ($check['is_suspicious'] && $user) {
            $this->notifyAdmin('⚠️ Suspicious signup', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'ip' => $ip,
                'reason' => $check['reason_internal'],
                'score' => $check['fraud_score'],
                'bonus_given' => $actualBonus,
            ]);
        }

        if ($bonusMessage) {
            session()->flash('signup_message', $bonusMessage);
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    protected function isStrongPassword(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password);
    }

    protected function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());
        return $code;
    }

    protected function notifyAdmin(string $title, array $data): void
    {
        try {
            $adminChatId = env('TELEGRAM_ADMIN_CHAT_ID');
            $botToken = env('TELEGRAM_BOT_TOKEN');

            if (!$adminChatId || !$botToken) return;

            $text = "<b>{$title}</b>\n\n";
            foreach ($data as $key => $value) {
                $text .= "<b>" . htmlspecialchars($key, ENT_QUOTES) . ":</b> "
                       . htmlspecialchars((string)$value, ENT_QUOTES) . "\n";
            }

            Http::timeout(3)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $adminChatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]
            );
        } catch (\Exception $e) {
            Log::warning("Admin notify failed: " . $e->getMessage());
        }
    }
}