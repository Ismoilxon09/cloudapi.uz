<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\VerifyEmailMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    /**
     * Tasdiqlash sahifasi — link yuborilganmi/yo'qmi
     */
    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->email_verified_at) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    /**
     * Tasdiqlash xat yuborish
     */
    public function send(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->email_verified_at) {
            return back()->with('success', 'Emailingiz allaqachon tasdiqlangan');
        }

        // Throttle — 1 daqiqada 1 ta
        $throttleKey = 'send_verify_email:' . $user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors(['email' => "Iltimos, {$seconds} soniya kuting"]);
        }
        RateLimiter::hit($throttleKey, 60);

        // Eski tokenni o'chirish
        DB::table('email_verification_tokens')->where('user_id', $user->id)->delete();

        // Yangi token
        $token = Str::random(60);
        DB::table('email_verification_tokens')->insert([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
        ]);

        // Email yuborish
        try {
            $verifyUrl = route('verification.verify', ['token' => $token]);
            Mail::to($user->email)->send(new VerifyEmailMail($user, $verifyUrl));
        } catch (\Exception $e) {
            \Log::error('Mail send failed: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Email yuborib bo\'lmadi. Iltimos, keyinroq urinib ko\'ring.']);
        }

        return back()->with('success', 'Tasdiqlash xati emailingizga yuborildi!');
    }

    /**
     * Email tasdiqlash linki orqali kirish
     */
    public function verify(string $token)
    {
        $hashedToken = hash('sha256', $token);

        $record = DB::table('email_verification_tokens')
            ->where('token', $hashedToken)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return redirect()->route('login')->withErrors([
                'email' => 'Tasdiqlash linki yaroqsiz yoki muddati o\'tgan',
            ]);
        }

        $user = User::find($record->user_id);
        if (!$user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Foydalanuvchi topilmadi',
            ]);
        }

        $user->update(['email_verified_at' => now()]);

        // Tokenni o'chirish
        DB::table('email_verification_tokens')->where('user_id', $user->id)->delete();

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Emailingiz muvaffaqiyatli tasdiqlandi! ✓');
    }
}