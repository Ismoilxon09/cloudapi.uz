<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\ProxyKey;
use App\Models\ProxyUsage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // === STATS ===
        $balance = $user->wallet?->balance_uzs ?? 0;

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $requestsToday = ProxyUsage::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->count();

        $monthData = ProxyUsage::where('user_id', $userId)
            ->where('created_at', '>=', $startOfMonth)
            ->selectRaw('
                COUNT(*) as requests,
                COALESCE(SUM(tokens_in + tokens_out), 0) as tokens,
                COALESCE(SUM(cost_uzs), 0) as spent,
                COALESCE(AVG(latency_ms), 0) as avg_latency
            ')
            ->first();

        $requestsMonth = $monthData->requests ?? 0;
        $tokensMonth = $monthData->tokens ?? 0;
        $spentMonth = $monthData->spent ?? 0;
        $avgLatency = round($monthData->avg_latency ?? 0);

        $activeKeysCount = ProxyKey::where('user_id', $userId)
            ->where('status', 'active')
            ->count();

        $totalKeysCount = ProxyKey::where('user_id', $userId)->count();

        // === USAGE CHART (last 7 days) ===
        $chartData = ProxyUsage::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as requests, COALESCE(SUM(cost_uzs), 0) as cost')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartRequests = [];
        $chartCost = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $label = Carbon::now()->subDays($i)->format('M d');
            $chartLabels[] = $label;
            $chartRequests[] = $chartData[$date]->requests ?? 0;
            $chartCost[] = round($chartData[$date]->cost ?? 0);
        }

        // === RECENT ACTIVITY ===
        $recentUsage = ProxyUsage::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // === TOP MODELS ===
        $topModels = ProxyUsage::where('user_id', $userId)
            ->where('created_at', '>=', $startOfMonth)
            ->selectRaw('model, COUNT(*) as cnt, COALESCE(SUM(cost_uzs), 0) as total_cost')
            ->groupBy('model')
            ->orderByDesc('cnt')
            ->limit(5)
            ->get();

        $topModelId = $topModels->first()->model ?? null;
        $topModelData = null;
        if ($topModelId) {
            $topModelData = AiModel::where('model_id', $topModelId)->first();
        }

        // === GETTING STARTED PROGRESS ===
        $hasKey = $totalKeysCount > 0;
        $hasBalance = $balance > 0;
        $hasRequest = ProxyUsage::where('user_id', $userId)->exists();
        $progress = ($hasKey ? 33 : 0) + ($hasBalance ? 33 : 0) + ($hasRequest ? 34 : 0);

        // === LOW BALANCE WARNING ===
        $lowBalance = $balance > 0 && $balance < 5000; // <5000 UZS

        return view('dashboard.index', compact(
            'user', 'balance',
            'requestsToday', 'requestsMonth', 'tokensMonth', 'spentMonth',
            'avgLatency', 'activeKeysCount', 'totalKeysCount',
            'chartLabels', 'chartRequests', 'chartCost',
            'recentUsage', 'topModels', 'topModelData',
            'hasKey', 'hasBalance', 'hasRequest', 'progress',
            'lowBalance'
        ));
    }
}