<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * OAuth provider'ga yo'naltirish
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, ['google', 'github'])) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * OAuth callback — provider'dan qaytadi
     */
    public function callback(string $provider)
    {
        if (!in_array($provider, ['google', 'github'])) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error("OAuth {$provider} error: " . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => "OAuth xato: {$e->getMessage()}",
            ]);
        }

        $providerId = $socialUser->getId();
        $email = $socialUser->getEmail();
        $name = $socialUser->getName() ?: $socialUser->getNickname() ?: 'User';
        $avatar = $socialUser->getAvatar();

        if (!$email) {
            return redirect()->route('login')->withErrors([
                'email' => "Email olinmadi. Iltimos, {$provider} hisobingizda email ko'rinishini sozlang.",
            ]);
        }

        // Avval shu provider ID'siga ega user'ni topish
        $providerField = "{$provider}_id";
        $user = User::where($providerField, $providerId)->first();

        if ($user) {
            // Mavjud user — login qilamiz
            if ($user->status === 'blocked') {
                return redirect()->route('login')->withErrors([
                    'email' => 'Akkaunt bloklangan',
                ]);
            }

            Auth::login($user, true);
            session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Email bo'yicha mavjud user'ni qidirish
        $user = User::where('email', strtolower($email))->first();

        if ($user) {
            // User bor — OAuth provider'ni qo'shamiz
            $user->update([
                $providerField => $providerId,
                'avatar' => $user->avatar ?: $avatar,
                'email_verified_at' => $user->email_verified_at ?: now(), // OAuth email tasdiqlangan
                'oauth_provider' => $user->oauth_provider ?: $provider,
            ]);

            Auth::login($user, true);
            session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Yangi user yaratamiz
        $newUser = DB::transaction(function () use ($providerId, $providerField, $email, $name, $avatar, $provider) {
            $user = User::create([
                'name' => strip_tags($name),
                'email' => strtolower($email),
                'password' => null, // OAuth user'larda parol yo'q
                'email_verified_at' => now(), // OAuth email tasdiqlangan
                $providerField => $providerId,
                'avatar' => $avatar,
                'oauth_provider' => $provider,
                'referral_code' => $this->generateReferralCode(),
                'role' => 'user',
                'status' => 'active',
                'language' => app()->getLocale(),
                'created_at' => now(),
            ]);

            // Wallet va bonus
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
                    'description' => "Ro'yxatdan o'tish bonusi ({$provider})",
                ]);
            }

            return $user;
        });

        Auth::login($newUser, true);
        session()->regenerate();

        return redirect()->route('dashboard');
    }

    protected function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());
        return $code;
    }
}