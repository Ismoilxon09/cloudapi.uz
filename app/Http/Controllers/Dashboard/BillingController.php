<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BillingController extends Controller
{
    /**
     * Wallet (asosiy sahifa)
     */
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // Wallet bo'lmasa, yarating
        if (!$wallet) {
            $wallet = $user->wallet()->create([
                'balance_uzs' => 0,
                'total_deposited' => 0,
                'total_spent' => 0,
            ]);
        }

        $transactions = $user->transactions()
            ->latest()
            ->paginate(20);

        return view('dashboard.billing.index', compact('wallet', 'transactions'));
    }

    /**
     * Top-up form
     */
    public function showTopup()
    {
        return view('dashboard.billing.topup');
    }

    /**
     * Top-up so'rovini yuborish (manual)
     */
    public function topup(Request $request)
    {
        // Brute force himoyasi
        $throttleKey = 'topup_attempts:' . auth()->id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors(['amount' => "Juda ko'p urinish. Birozdan keyin urinib ko'ring."]);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 3600);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10000|max:50000000', // max 50M so'm
            'method' => 'required|in:manual,payme,click',
            'receipt' => [
                'required_if:method,manual',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120', // 5MB
                'dimensions:max_width=4000,max_height=4000',
            ],
        ]);

        $user = Auth::user();

        // Bloklangan user tekshiruvi
        if ($user->status === 'blocked') {
            return back()->withErrors(['amount' => 'Akkaunt bloklangan']);
        }

        if ($validated['method'] !== 'manual') {
            return back()->withErrors([
                'method' => 'This payment method is coming soon',
            ]);
        }

        // Screenshot ni saqlash — kuchli validatsiya
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');

            // Real MIME tekshirish (extension emas, file content)
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
            $realMime = $file->getMimeType();
            if (!in_array($realMime, $allowedMimes)) {
                return back()->withErrors(['receipt' => 'Faqat JPG/PNG fayllar qabul qilinadi']);
            }

            // Random nom (path traversal himoyasi)
            $filename = \Illuminate\Support\Str::random(40) . '.' . $file->extension();
            $receiptPath = $file->storeAs("receipts/{$user->id}", $filename, 'public');
        }

        // Pending tranzaksiya yaratish (admin tasdiqlashini kutadi)
        $wallet = $user->wallet ?? $user->wallet()->create([
            'balance_uzs' => 0,
            'total_deposited' => 0,
            'total_spent' => 0,
        ]);

        $wallet->transactions()->create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'status' => 'pending',
            'amount_uzs' => $validated['amount'],
            'balance_after' => $wallet->balance_uzs, // hali qo'shilmadi
            'payment_method' => 'manual',
            'reference' => $receiptPath,
            'description' => "Manual top-up request — pending admin approval",
        ]);

        // Admin ga bildirishnoma yuborish
        \App\Models\AdminNotification::notify(
            'new_topup',
            "Yangi to'lov so'rovi",
            "{$user->name} — " . number_format($validated['amount'], 0, '.', ' ') . " so'm",
            ['user_id' => $user->id, 'amount' => $validated['amount']],
            'high',
            '/admin/payments'
        );

        return redirect()->route('billing.index')->with(
            'success',
            __('billing.topup.pending')
        );
    }
}