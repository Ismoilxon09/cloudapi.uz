<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ProxyUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Time range
        $range = $request->get('range', '7d');
        $days = match($range) {
            '24h' => 1,
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 7,
        };
        $since = Carbon::now()->subDays($days)->startOfDay();

        // Stats
        $stats = ProxyUsage::where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->selectRaw('
                COUNT(*) as requests,
                COALESCE(SUM(tokens_in + tokens_out), 0) as tokens,
                COALESCE(SUM(cost_uzs), 0) as spent,
                COALESCE(AVG(latency_ms), 0) as avg_latency,
                COALESCE(SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END), 0) as success,
                COALESCE(SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END), 0) as errors
            ')
            ->first();

        // Chart data
        $chartData = ProxyUsage::where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as requests, COALESCE(SUM(cost_uzs),0) as cost, COALESCE(SUM(tokens_in+tokens_out),0) as tokens')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartRequests = [];
        $chartCost = [];
        $chartTokens = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $label = $days <= 7
                ? Carbon::now()->subDays($i)->format('D M d')
                : Carbon::now()->subDays($i)->format('M d');
            $chartLabels[] = $label;
            $chartRequests[] = $chartData[$date]->requests ?? 0;
            $chartCost[] = round($chartData[$date]->cost ?? 0);
            $chartTokens[] = $chartData[$date]->tokens ?? 0;
        }

        // Top models
        $topModels = ProxyUsage::where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->selectRaw('model, COUNT(*) as cnt, COALESCE(SUM(tokens_in+tokens_out),0) as tokens, COALESCE(SUM(cost_uzs),0) as cost')
            ->groupBy('model')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        return view('dashboard.activity.index', compact(
            'stats', 'chartLabels', 'chartRequests', 'chartCost', 'chartTokens',
            'topModels', 'range', 'days'
        ));
    }
}