<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\ProxyUsage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'user')
            ->withCount(['proxyKeys', 'transactions']);

        // Filter
        $filter = $request->get('filter', 'all');
        switch ($filter) {
            case 'active':
                $query->where('status', 'active');
                break;
            case 'blocked':
                $query->where('status', 'blocked');
                break;
            case 'new':
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
                break;
        }

        // Search
        if ($search = $request->get('q')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'spent' => $query->withSum('transactions as total_spent', 'amount_uzs')->orderByDesc('total_spent'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        $users = $query->paginate(20)->withQueryString();

        $counts = [
            'all' => User::where('role', 'user')->count(),
            'active' => User::where('role', 'user')->where('status', 'active')->count(),
            'blocked' => User::where('role', 'user')->where('status', 'blocked')->count(),
            'new' => User::where('role', 'user')->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        return view('admin.users.index', compact('users', 'filter', 'counts'));
    }

    public function show(User $user)
    {
        $user->loadCount(['proxyKeys', 'transactions']);

        $stats = [
            'total_requests' => ProxyUsage::where('user_id', $user->id)->count(),
            'total_tokens' => ProxyUsage::where('user_id', $user->id)->sum(\DB::raw('tokens_in + tokens_out')),
            'total_spent' => ProxyUsage::where('user_id', $user->id)->sum('cost_uzs'),
            'requests_today' => ProxyUsage::where('user_id', $user->id)->whereDate('created_at', today())->count(),
            'deposits_total' => Transaction::where('user_id', $user->id)->where('type', 'deposit')->where('status', 'completed')->sum('amount_uzs'),
            'deposits_pending' => Transaction::where('user_id', $user->id)->where('type', 'deposit')->where('status', 'pending')->count(),
        ];

        $recentTransactions = Transaction::where('user_id', $user->id)->latest()->limit(10)->get();
        $keys = $user->proxyKeys()->latest()->get();

        return view('admin.users.show', compact('user', 'stats', 'recentTransactions', 'keys'));
    }

    public function adjustBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'required|string|max:200',
            'type' => 'required|in:credit,debit,bonus',
        ]);

        $amount = abs($validated['amount']);
        $isCredit = in_array($validated['type'], ['credit', 'bonus']);
        $actualAmount = $isCredit ? $amount : -$amount;

        $wallet = $user->wallet ?? $user->wallet()->create([
            'balance_uzs' => 0,
            'total_deposited' => 0,
            'total_spent' => 0,
        ]);

        if (!$isCredit && $wallet->balance_uzs < $amount) {
            return back()->with('error', "User balansi yetarli emas");
        }

        \DB::transaction(function () use ($wallet, $actualAmount, $isCredit, $validated, $user) {
            $wallet->increment('balance_uzs', $actualAmount);
            if ($isCredit) $wallet->increment('total_deposited', abs($actualAmount));
            else $wallet->increment('total_spent', abs($actualAmount));

            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => $validated['type'] === 'bonus' ? 'bonus' : ($isCredit ? 'deposit' : 'withdrawal'),
                'status' => 'completed',
                'amount_uzs' => $actualAmount,
                'balance_after' => $wallet->fresh()->balance_uzs,
                'payment_method' => 'admin',
                'description' => "Admin: {$validated['reason']}",
                'admin_id' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            AdminLog::record('balance_adjusted', $user,
                "{$user->name}: " . ($isCredit ? '+' : '-') . number_format($amount, 0, '.', ' ') . " so'm. {$validated['reason']}",
                ['amount' => $actualAmount, 'type' => $validated['type']]
            );
        });

        return back()->with('success', "Balans muvaffaqiyatli o'zgartirildi");
    }

    public function block(User $user)
    {
        $user->update(['status' => 'blocked']);
        AdminLog::record('user_blocked', $user, "{$user->name} bloklandi");

        // Hamma API kalitlarni revoke qilish
        $user->proxyKeys()->where('status', 'active')->update(['status' => 'revoked']);

        return back()->with('success', "Foydalanuvchi bloklandi");
    }

    public function unblock(User $user)
    {
        $user->update(['status' => 'active']);
        AdminLog::record('user_unblocked', $user, "{$user->name} blokdan chiqarildi");
        return back()->with('success', "Foydalanuvchi blokdan chiqarildi");
    }
}