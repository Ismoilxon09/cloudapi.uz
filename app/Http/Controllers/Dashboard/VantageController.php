<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AgentMessage;
use App\Models\ProxyUsage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Vantage — CloudAPI observability hub.
 * Foydalanuvchining barcha AI faoliyatini (API proxy + agentlar) jonli kuzatadi.
 */
class VantageController extends Controller
{
    public function index()
    {
        return view('dashboard.vantage.index', $this->snapshot());
    }

    /** AI Town — jonli spatial vizualizatsiya. */
    public function town()
    {
        $agents = Auth::user()->agents()->where('status', 'active')->get(['id', 'name', 'behavior_preset', 'model_slug']);
        $s = $this->snapshot();

        return view('dashboard.vantage.town', [
            'agents'    => $agents,
            'kpis'      => $s['kpis'],
            'recent'    => $s['recent'],
            'topModels' => $s['topModels'],
        ]);
    }

    /** Jonli yangilanish uchun JSON. */
    public function stream()
    {
        $s = $this->snapshot();
        return response()->json([
            'kpis'   => $s['kpis'],
            'recent' => $s['recent'],
            'series' => $s['series'],
        ]);
    }

    /** Barcha ko'rsatkichlarni bir joyda. */
    protected function snapshot(): array
    {
        $uid = Auth::id();
        $agentIds = Auth::user()->agents()->pluck('id')->all();

        return [
            'kpis'      => $this->kpis($uid, $agentIds),
            'recent'    => $this->recent($uid, $agentIds, 40),
            'topModels' => $this->topModels($uid, $agentIds),
            'series'    => $this->series($uid, $agentIds),
        ];
    }

    protected function kpis(int $uid, array $agentIds): array
    {
        $today = Carbon::today();

        $px = ProxyUsage::where('user_id', $uid)->whereDate('created_at', $today)
            ->selectRaw('COUNT(*) c, COALESCE(SUM(tokens_in+tokens_out),0) t, COALESCE(SUM(cost_uzs),0) cost')
            ->first();

        $ag = AgentMessage::whereIn('agent_id', $agentIds ?: [0])
            ->where('role', 'assistant')->whereDate('created_at', $today)
            ->selectRaw('COUNT(*) c, COALESCE(SUM(tokens_input+tokens_output),0) t, COALESCE(SUM(cost_uzs),0) cost, COUNT(DISTINCT agent_id) agents')
            ->first();

        // Aniq distinct modellar (ikkala manba bo'ylab)
        $models = ProxyUsage::where('user_id', $uid)->whereDate('created_at', $today)->distinct()->pluck('model')
            ->merge(AgentMessage::whereIn('agent_id', $agentIds ?: [0])->where('role', 'assistant')
                ->whereDate('created_at', $today)->distinct()->pluck('model_id'))
            ->filter()->unique()->count();

        return [
            'requests'      => (int) $px->c + (int) $ag->c,
            'tokens'        => (int) $px->t + (int) $ag->t,
            'cost_uzs'      => round((float) $px->cost + (float) $ag->cost, 2),
            'models'        => $models,
            'agents_active' => (int) $ag->agents,
        ];
    }

    /** So'nggi hodisalar (API + agent) — vaqt bo'yicha birlashtirilgan. */
    protected function recent(int $uid, array $agentIds, int $limit): array
    {
        $events = [];

        foreach (ProxyUsage::where('user_id', $uid)->latest('created_at')->limit($limit)->get() as $r) {
            $events[] = [
                'source'  => 'API',
                'model'   => $r->model,
                'tokens'  => (int) $r->tokens_in + (int) $r->tokens_out,
                'cost'    => round((float) $r->cost_uzs, 2),
                'status'  => (int) $r->status_code === 200 ? 'ok' : 'err',
                'latency' => (int) $r->latency_ms,
                'at'      => $r->created_at?->toIso8601String(),
                'ts'      => $r->created_at?->timestamp ?? 0,
            ];
        }

        foreach (AgentMessage::whereIn('agent_id', $agentIds ?: [0])->where('role', 'assistant')
                     ->latest('created_at')->limit($limit)->get() as $m) {
            $events[] = [
                'source'  => 'Agent',
                'model'   => $m->model_id,
                'tokens'  => (int) $m->tokens_input + (int) $m->tokens_output,
                'cost'    => round((float) $m->cost_uzs, 2),
                'status'  => 'ok',
                'latency' => (int) $m->latency_ms,
                'at'      => $m->created_at?->toIso8601String(),
                'ts'      => $m->created_at?->timestamp ?? 0,
            ];
        }

        usort($events, fn ($a, $b) => $b['ts'] <=> $a['ts']);
        return array_slice($events, 0, $limit);
    }

    /** Top modellar (so'nggi 7 kun) — so'rovlar soni + xarajat. */
    protected function topModels(int $uid, array $agentIds): array
    {
        $since = Carbon::now()->subDays(7);
        $agg = [];

        foreach (ProxyUsage::where('user_id', $uid)->where('created_at', '>=', $since)
                     ->selectRaw('model, COUNT(*) c, COALESCE(SUM(cost_uzs),0) cost')
                     ->groupBy('model')->get() as $r) {
            $agg[$r->model] = ['model' => $r->model, 'count' => (int) $r->c, 'cost' => (float) $r->cost];
        }

        foreach (AgentMessage::whereIn('agent_id', $agentIds ?: [0])->where('role', 'assistant')
                     ->where('created_at', '>=', $since)
                     ->selectRaw('model_id, COUNT(*) c, COALESCE(SUM(cost_uzs),0) cost')
                     ->groupBy('model_id')->get() as $r) {
            $k = $r->model_id ?: 'unknown';
            if (isset($agg[$k])) {
                $agg[$k]['count'] += (int) $r->c;
                $agg[$k]['cost']  += (float) $r->cost;
            } else {
                $agg[$k] = ['model' => $k, 'count' => (int) $r->c, 'cost' => (float) $r->cost];
            }
        }

        usort($agg, fn ($a, $b) => $b['count'] <=> $a['count']);
        return array_slice(array_map(fn ($x) => [
            'model' => $x['model'], 'count' => $x['count'], 'cost' => round($x['cost'], 2),
        ], array_values($agg)), 0, 8);
    }

    /** So'nggi 24 soat — soatlik xarajat (UZS). */
    protected function series(int $uid, array $agentIds): array
    {
        $start = Carbon::now()->subHours(23)->startOfHour();

        $buckets = [];
        for ($i = 0; $i < 24; $i++) {
            $h = (clone $start)->addHours($i);
            $buckets[$h->format('Y-m-d H')] = ['label' => $h->format('H:00'), 'cost' => 0.0, 'requests' => 0];
        }

        // SQL bucket (%Y-%m-%d %H) PHP kalit formati bilan bir xil — to'g'ridan-to'g'ri ishlatamiz
        $fill = function ($rows) use (&$buckets) {
            foreach ($rows as $r) {
                $k = $r->bucket;
                if (isset($buckets[$k])) {
                    $buckets[$k]['cost'] += (float) $r->cost;
                    $buckets[$k]['requests'] += (int) $r->c;
                }
            }
        };

        $fill(ProxyUsage::where('user_id', $uid)->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d %H') bucket, COUNT(*) c, COALESCE(SUM(cost_uzs),0) cost")
            ->groupBy('bucket')->get());

        $fill(AgentMessage::whereIn('agent_id', $agentIds ?: [0])->where('role', 'assistant')
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d %H') bucket, COUNT(*) c, COALESCE(SUM(cost_uzs),0) cost")
            ->groupBy('bucket')->get());

        return array_map(fn ($b) => [
            'label' => $b['label'], 'cost' => round($b['cost'], 2), 'requests' => $b['requests'],
        ], array_values($buckets));
    }
}
