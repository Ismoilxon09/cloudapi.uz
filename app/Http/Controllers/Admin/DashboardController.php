<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\AiModel;
use App\Models\ProxyKey;
use App\Models\ProxyUsage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->startOfMonth();

        // Foydalanuvchilar
        $totalUsers = User::where('role', 'user')->count();
        $newUsersToday = User::where('role', 'user')->whereDate('created_at', $today)->count();
        $newUsersYesterday = User::where('role', 'user')->whereDate('created_at', $yesterday)->count();
        $activeToday = ProxyUsage::whereDate('created_at', $today)->distinct('user_id')->count('user_id');

        // To'lovlar
        $pendingPayments = Transaction::where('type', 'deposit')->where('status', 'pending')->count();
        $pendingAmount = Transaction::where('type', 'deposit')->where('status', 'pending')->sum('amount_uzs');
        $todayRevenue = Transaction::where('type', 'deposit')
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('amount_uzs');
        $monthRevenue = Transaction::where('type', 'deposit')
            ->where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount_uzs');

        // So'rovlar
        $requestsToday = ProxyUsage::whereDate('created_at', $today)->count();
        $requestsYesterday = ProxyUsage::whereDate('created_at', $yesterday)->count();
        $requestsMonth = ProxyUsage::where('created_at', '>=', $thisMonth)->count();

        // Tokenlar
        $tokensToday = ProxyUsage::whereDate('created_at', $today)
            ->sum(DB::raw('tokens_in + tokens_out'));
        $tokensMonth = ProxyUsage::where('created_at', '>=', $thisMonth)
            ->sum(DB::raw('tokens_in + tokens_out'));

        // Foyda (marja)
        $costSpent = ProxyUsage::where('created_at', '>=', $thisMonth)->sum('cost_uzs');
        $costMonth = $costSpent; // hammasi marja bilan, foydani hisoblash kerak

        // Modellar
        $totalModels = AiModel::count();
        $activeModels = AiModel::where('active', true)->count();

        // API kalitlar
        $totalKeys = ProxyKey::count();
        $activeKeys = ProxyKey::where('status', 'active')->count();

        // O'sish (kunlik)
        $userGrowth = $newUsersYesterday > 0
            ? round((($newUsersToday - $newUsersYesterday) / $newUsersYesterday) * 100, 1)
            : ($newUsersToday > 0 ? 100 : 0);

        $requestGrowth = $requestsYesterday > 0
            ? round((($requestsToday - $requestsYesterday) / $requestsYesterday) * 100, 1)
            : ($requestsToday > 0 ? 100 : 0);

        // Chart: oxirgi 14 kun
        $chartData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartData[] = [
                'date' => $date->format('M d'),
                'users' => User::where('role', 'user')->whereDate('created_at', $date)->count(),
                'revenue' => (float)Transaction::where('type', 'deposit')
                    ->where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('amount_uzs'),
                'requests' => ProxyUsage::whereDate('created_at', $date)->count(),
            ];
        }

        // Pending payments (top 5)
        $pendingList = Transaction::with('user')
            ->where('type', 'deposit')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        // Top users (by spending this month)
        $topUsers = User::where('role', 'user')
            ->select('users.*')
            ->selectRaw('(SELECT COALESCE(SUM(cost_uzs),0) FROM proxy_usage WHERE proxy_usage.user_id = users.id AND created_at >= ?) as month_spent', [$thisMonth])
            ->orderByDesc('month_spent')
            ->limit(5)
            ->get();

        // Recent notifications
        $notifications = AdminNotification::orderByDesc('created_at')->limit(8)->get();

        return view('admin.dashboard.index', compact(
            'totalUsers', 'newUsersToday', 'activeToday',
            'pendingPayments', 'pendingAmount',
            'todayRevenue', 'monthRevenue',
            'requestsToday', 'requestsMonth', 'tokensToday', 'tokensMonth',
            'totalModels', 'activeModels', 'totalKeys', 'activeKeys',
            'userGrowth', 'requestGrowth',
            'chartData', 'pendingList', 'topUsers', 'notifications'
        ));
    }
}