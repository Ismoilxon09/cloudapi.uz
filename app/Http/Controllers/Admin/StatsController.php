<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProxyUsage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->get('range', '30d');
        $days = match($range) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            '365d' => 365,
            default => 30,
        };

        $since = Carbon::now()->subDays($days)->startOfDay();

        // Umumiy
        $totalRevenue = Transaction::where('type', 'deposit')
            ->where('status', 'completed')
            ->where('created_at', '>=', $since)
            ->sum('amount_uzs');

        $totalCostToOpenRouter = ProxyUsage::where('created_at', '>=', $since)
            ->sum(DB::raw('cost_usd * 12700')); // USD ni so'mga

        // Sof marja (foyda) = users sarflagan - bizning OpenRouter ga to'lagan
        $totalSpent = ProxyUsage::where('created_at', '>=', $since)->sum('cost_uzs');
        $estimatedProfit = $totalSpent - $totalCostToOpenRouter;

        $totalRequests = ProxyUsage::where('created_at', '>=', $since)->count();
        $totalUsers = User::where('role', 'user')->where('created_at', '>=', $since)->count();
        $activeUsers = ProxyUsage::where('created_at', '>=', $since)->distinct('user_id')->count('user_id');

        // Daily breakdown
        $daily = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $daily[] = [
                'date' => $date->format('M d'),
                'revenue' => (float)Transaction::where('type', 'deposit')->where('status', 'completed')->whereDate('created_at', $date)->sum('amount_uzs'),
                'spent' => (float)ProxyUsage::whereDate('created_at', $date)->sum('cost_uzs'),
                'requests' => ProxyUsage::whereDate('created_at', $date)->count(),
                'users' => User::where('role', 'user')->whereDate('created_at', $date)->count(),
            ];
        }

        // Top models
        $topModels = ProxyUsage::where('created_at', '>=', $since)
            ->selectRaw('model, COUNT(*) as cnt, SUM(tokens_in+tokens_out) as tokens, SUM(cost_uzs) as revenue')
            ->groupBy('model')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Top users
        $topUsers = User::where('role', 'user')
            ->select('users.*')
            ->selectRaw('(SELECT COALESCE(SUM(cost_uzs),0) FROM proxy_usage WHERE proxy_usage.user_id = users.id AND created_at >= ?) as period_spent', [$since])
            ->orderByDesc('period_spent')
            ->limit(10)
            ->get();

        return view('admin.stats.index', compact(
            'totalRevenue', 'totalCostToOpenRouter', 'estimatedProfit',
            'totalSpent', 'totalRequests', 'totalUsers', 'activeUsers',
            'daily', 'topModels', 'topUsers', 'range', 'days'
        ));
    }

    public function revenue(Request $request)
    {
        // Detailed revenue page (later)
        return $this->index($request);
    }
}