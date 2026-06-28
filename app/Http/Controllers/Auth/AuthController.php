<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
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

            if (Auth::user()->status === 'blocked') {
                Auth::logout();
                return back()->withErrors(['email' => 'Akkaunt bloklangan']);
            }

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
        ]);

        if (!$this->isStrongPassword($validated['password'])) {
            return back()->withErrors([
                'password' => "Parol kuchli emas. Katta harf, kichik harf va raqam bo'lishi kerak.",
            ])->onlyInput('name', 'email');
        }

        DB::transaction(function () use ($validated, $request) {
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
                'created_at' => now(),
            ]);

            $signupBonus = (float)\App\Models\SystemSetting::get('signup_bonus_uzs', 0);
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance_uzs' => $signupBonus,
                'total_deposited' => 0,
                'total_spent' => 0,
            ]);

            if ($signupBonus > 0) {
                $user->transactions()->create([
                    'wallet_id' => $wallet->id,
                    'type' => 'bonus',
                    'status' => 'completed',
                    'amount_uzs' => $signupBonus,
                    'balance_after' => $signupBonus,
                    'description' => "Ro'yxatdan o'tish bonusi",
                ]);
            }

            if ($referredBy) {
                $referralBonus = (float)\App\Models\SystemSetting::get('referral_bonus_uzs', 0);
                if ($referralBonus > 0) {
                    $referrer = User::find($referredBy);
                    $referrer->wallet?->increment('balance_uzs', $referralBonus);
                    $referrer->transactions()->create([
                        'wallet_id' => $referrer->wallet?->id,
                        'type' => 'bonus',
                        'status' => 'completed',
                        'amount_uzs' => $referralBonus,
                        'description' => "Referral bonus: {$user->name}",
                    ]);
                }
            }

            Auth::login($user);
            $request->session()->regenerate();
        });

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
}