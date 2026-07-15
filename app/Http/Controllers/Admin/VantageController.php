<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentMessage;
use App\Models\ProxyUsage;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Admin Vantage — BUTUN platforma bo'yicha jonli AI kuzatuv.
 */
class VantageController extends Controller
{
    public function index()
    {
        return view('admin.vantage.index', $this->snapshot());
    }

    public function stream()
    {
        $s = $this->snapshot();
        return response()->json(['kpis' => $s['kpis'], 'recent' => $s['recent'], 'series' => $s['series']]);
    }

    protected function snapshot(): array
    {
        return [
            'kpis'      => $this->kpis(),
            'recent'    => $this->recent(40),
            'topModels' => $this->topModels(),
            'topUsers'  => $this->topUsers(),
            'series'    => $this->series(),
        ];
    }

    protected function kpis(): array
    {
        $today = Carbon::today();

        $px = ProxyUsage::whereDate('created_at', $today)
            ->selectRaw('COUNT(*) c, COALESCE(SUM(tokens_in+tokens_out),0) t, COALESCE(SUM(cost_uzs),0) cost, COUNT(DISTINCT user_id) u')->first();
        $ag = AgentMessage::where('role', 'assistant')->whereDate('created_at', $today)
            ->selectRaw('COUNT(*) c, COALESCE(SUM(tokens_input+tokens_output),0) t, COALESCE(SUM(cost_uzs),0) cost')->first();

        return [
            'requests' => (int) $px->c + (int) $ag->c,
            'tokens'   => (int) $px->t + (int) $ag->t,
            'cost_uzs' => round((float) $px->cost + (float) $ag->cost, 2),
            'users'    => (int) $px->u,
            'agents'   => \App\Models\Agent::where('status', 'active')->count(),
        ];
    }

    protected function recent(int $limit): array
    {
        $events = [];
        foreach (ProxyUsage::latest('created_at')->limit($limit)->get() as $r) {
            $events[] = ['source' => 'API', 'model' => $r->model, 'tokens' => (int) $r->tokens_in + (int) $r->tokens_out,
                'cost' => round((float) $r->cost_uzs, 2), 'at' => $r->created_at?->toIso8601String(), 'ts' => $r->created_at?->timestamp ?? 0];
        }
        foreach (AgentMessage::where('role', 'assistant')->latest('created_at')->limit($limit)->get() as $m) {
            $events[] = ['source' => 'Agent', 'model' => $m->model_id, 'tokens' => (int) $m->tokens_input + (int) $m->tokens_output,
                'cost' => round((float) $m->cost_uzs, 2), 'at' => $m->created_at?->toIso8601String(), 'ts' => $m->created_at?->timestamp ?? 0];
        }
        usort($events, fn ($a, $b) => $b['ts'] <=> $a['ts']);
        return array_slice($events, 0, $limit);
    }

    protected function topModels(): array
    {
        $since = Carbon::now()->subDays(7);
        $agg = [];
        foreach (ProxyUsage::where('created_at', '>=', $since)->selectRaw('model, COUNT(*) c, COALESCE(SUM(cost_uzs),0) cost')->groupBy('model')->get() as $r) {
            $agg[$r->model] = ['model' => $r->model, 'count' => (int) $r->c, 'cost' => (float) $r->cost];
        }
        foreach (AgentMessage::where('role', 'assistant')->where('created_at', '>=', $since)->selectRaw('model_id, COUNT(*) c, COALESCE(SUM(cost_uzs),0) cost')->groupBy('model_id')->get() as $r) {
            $k = $r->model_id ?: 'unknown';
            if (isset($agg[$k])) { $agg[$k]['count'] += (int) $r->c; $agg[$k]['cost'] += (float) $r->cost; }
            else { $agg[$k] = ['model' => $k, 'count' => (int) $r->c, 'cost' => (float) $r->cost]; }
        }
        usort($agg, fn ($a, $b) => $b['count'] <=> $a['count']);
        return array_slice(array_map(fn ($x) => ['model' => $x['model'], 'count' => $x['count'], 'cost' => round($x['cost'], 2)], array_values($agg)), 0, 8);
    }

    protected function topUsers(): array
    {
        $since = Carbon::now()->subDays(7);
        $rows = ProxyUsage::where('created_at', '>=', $since)
            ->selectRaw('user_id, COUNT(*) c, COALESCE(SUM(cost_uzs),0) cost')
            ->groupBy('user_id')->orderByDesc('cost')->limit(8)->get();
        $names = User::whereIn('id', $rows->pluck('user_id'))->pluck('name', 'id');
        return $rows->map(fn ($r) => [
            'name'  => $names[$r->user_id] ?? ('#' . $r->user_id),
            'count' => (int) $r->c,
            'cost'  => round((float) $r->cost, 2),
        ])->all();
    }

    protected function series(): array
    {
        $start = Carbon::now()->subHours(23)->startOfHour();
        $buckets = [];
        for ($i = 0; $i < 24; $i++) { $h = (clone $start)->addHours($i); $buckets[$h->format('Y-m-d H')] = ['label' => $h->format('H:00'), 'cost' => 0.0, 'requests' => 0]; }
        $fill = function ($rows) use (&$buckets) {
            foreach ($rows as $r) { if (isset($buckets[$r->bucket])) { $buckets[$r->bucket]['cost'] += (float) $r->cost; $buckets[$r->bucket]['requests'] += (int) $r->c; } }
        };
        $fill(ProxyUsage::where('created_at', '>=', $start)->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d %H') bucket, COUNT(*) c, COALESCE(SUM(cost_uzs),0) cost")->groupBy('bucket')->get());
        $fill(AgentMessage::where('role', 'assistant')->where('created_at', '>=', $start)->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d %H') bucket, COUNT(*) c, COALESCE(SUM(cost_uzs),0) cost")->groupBy('bucket')->get());
        return array_map(fn ($b) => ['label' => $b['label'], 'cost' => round($b['cost'], 2), 'requests' => $b['requests']], array_values($buckets));
    }
}
