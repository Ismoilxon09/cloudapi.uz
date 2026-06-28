<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * "Parol unutdim" forma
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Email yuborish
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower($request->email);

        // Throttle — IP+email ga 3 ta urinish/soat
        $throttleKey = 'reset_password:' . $request->ip() . '|' . $email;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return back()->withErrors([
                'email' => 'Juda ko\'p urinish. Birozdan keyin urinib ko\'ring.',
            ]);
        }
        RateLimiter::hit($throttleKey, 3600);

        $user = User::where('email', $email)->first();

        // Xavfsizlik: user mavjudligi haqida xabar bermaymiz
        // (account enumeration himoyasi)
        if ($user && !$user->password) {
            // OAuth user — parol yo'q
            return back()->with('success', 'Agar bu email tizimda bo\'lsa, parol tiklash xati yuborildi. (Eslatma: OAuth orqali kirgan akkauntlar uchun parol kerak emas)');
        }

        if ($user) {
            // Eski tokenni o'chirish
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            // Yangi token
            $token = Str::random(60);
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

            try {
                $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);
                Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));
            } catch (\Exception $e) {
                \Log::error('Reset mail failed: ' . $e->getMessage());
            }
        }

        // Har doim bir xil javob (security)
        return back()->with('success', 'Agar bu email tizimda bo\'lsa, parol tiklash xati yuborildi.');
    }

    /**
     * Parolni qayta o'rnatish formasi
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Yangi parolni saqlash
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Kuchli parol tekshiruvi
        if (!$this->isStrongPassword($request->password)) {
            return back()->withErrors([
                'password' => 'Parol kuchli emas. Katta harf, kichik harf va raqam bo\'lishi kerak.',
            ])->onlyInput('email');
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', strtolower($request->email))
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors([
                'email' => 'Token yaroqsiz yoki muddati o\'tgan',
            ]);
        }

        // Token muddati — 60 daqiqa
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $record->email)->delete();
            return back()->withErrors([
                'email' => 'Token muddati o\'tgan. Yana so\'rang.',
            ]);
        }

        $user = User::where('email', strtolower($request->email))->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Foydalanuvchi topilmadi']);
        }

        // Parolni yangilash
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Token o'chirish
        DB::table('password_reset_tokens')->where('email', $record->email)->delete();

        return redirect()->route('login')->with('success', 'Parolingiz muvaffaqiyatli yangilandi! Endi kirishingiz mumkin.');
    }

    protected function isStrongPassword(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password);
    }
}