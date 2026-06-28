@extends('layouts.app')

@section('title', __('activity.title') . ' — CloudAPI')

@push('styles')
<style>
.activity-page { max-width: 1400px; margin: 0 auto; padding: 24px; }

.act-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.act-title { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-strong); margin-bottom: 4px; }
.act-subtitle { font-size: 13px; color: var(--text-muted); }

.range-tabs {
  display: flex;
  gap: 4px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 3px;
}

.range-tab {
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  border-radius: 6px;
  cursor: pointer;
  text-decoration: none;
}

.range-tab:hover { color: var(--text-strong); }
.range-tab.active { background: var(--text-strong); color: var(--bg); }

.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}

.stat-tile {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 18px;
}

.stat-label {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 8px;
}

.stat-value {
  font-size: 22px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
}

.stat-value .meta { font-size: 13px; color: var(--text-muted); font-weight: 500; margin-left: 3px; }

.stat-icon {
  width: 26px; height: 26px; border-radius: 6px;
  background: var(--bg-subtle);
  display: inline-flex; align-items: center; justify-content: center;
  float: right;
}

.stat-icon .material-icons-round { font-size: 14px; color: var(--text-muted); }

.dash-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 22px;
  margin-bottom: 16px;
}

.dash-card-title { font-size: 14px; font-weight: 700; color: var(--text-strong); margin-bottom: 18px; }

.chart-wrap { height: 280px; }

.act-layout {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 16px;
}

.top-list { display: flex; flex-direction: column; gap: 6px; }

.top-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: var(--bg-subtle);
  border-radius: 8px;
  text-decoration: none;
  color: inherit;
}

.top-item:hover { background: var(--bg-hover); }

.top-rank {
  width: 22px; height: 22px; border-radius: 5px;
  background: var(--gray-deep);
  color: var(--bg);
  display: flex; align-items: center; justify-content: center;
  font-size: 10px; font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  flex-shrink: 0;
}

[data-theme="dark"] .top-rank { background: var(--text-strong); color: var(--bg); }

.top-info { flex: 1; min-width: 0; }
.top-name { font-size: 12px; font-weight: 600; color: var(--text-strong); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.top-meta { font-size: 10px; color: var(--text-muted); margin-top: 1px; }
.top-cost {
  font-size: 12px; font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  color: var(--text-strong);
}

.empty {
  text-align: center; padding: 40px 20px; color: var(--text-muted);
}

.empty .material-icons-round {
  font-size: 40px; color: var(--text-subtle); margin-bottom: 12px; opacity: 0.5;
}

@media (max-width: 900px) {
  .act-layout { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="activity-page">
  <div class="act-header">
    <div>
      <h1 class="act-title">{{ __('activity.title') }}</h1>
      <p class="act-subtitle">{{ __('activity.subtitle') }}</p>
    </div>
    <div class="range-tabs">
      @foreach(['24h', '7d', '30d', '90d'] as $r)
        <a href="?range={{ $r }}" class="range-tab {{ $range === $r ? 'active' : '' }}">{{ __("activity.ranges.{$r}") }}</a>
      @endforeach
    </div>
  </div>

  <!-- Stats -->
  <div class="stat-grid">
    <div class="stat-tile">
      <div class="stat-icon"><span class="material-icons-round">send</span></div>
      <div class="stat-label">{{ __('activity.stats.total_requests') }}</div>
      <div class="stat-value">{{ number_format($stats->requests) }}</div>
    </div>
    <div class="stat-tile">
      <div class="stat-icon"><span class="material-icons-round">data_usage</span></div>
      <div class="stat-label">{{ __('activity.stats.total_tokens') }}</div>
      <div class="stat-value">
        @if($stats->tokens >= 1000000)
          {{ number_format($stats->tokens / 1000000, 2) }}<span class="meta">M</span>
        @elseif($stats->tokens >= 1000)
          {{ number_format($stats->tokens / 1000, 1) }}<span class="meta">K</span>
        @else
          {{ number_format($stats->tokens) }}
        @endif
      </div>
    </div>
    <div class="stat-tile">
      <div class="stat-icon"><span class="material-icons-round">payments</span></div>
      <div class="stat-label">{{ __('activity.stats.total_spent') }}</div>
      <div class="stat-value">{{ number_format($stats->spent, 0, '.', ' ') }}<span class="meta">{{ __('common.currency') }}</span></div>
    </div>
    <div class="stat-tile">
      <div class="stat-icon"><span class="material-icons-round">speed</span></div>
      <div class="stat-label">{{ __('activity.stats.avg_latency') }}</div>
      <div class="stat-value">{{ round($stats->avg_latency) }}<span class="meta">ms</span></div>
    </div>
    <div class="stat-tile">
      <div class="stat-icon" style="background:rgba(16,185,129,.12)"><span class="material-icons-round" style="color:var(--success)">check_circle</span></div>
      <div class="stat-label">{{ __('activity.stats.success_rate') }}</div>
      <div class="stat-value">{{ number_format($stats->success) }}</div>
    </div>
    <div class="stat-tile">
      <div class="stat-icon" style="background:rgba(239,68,68,.12)"><span class="material-icons-round" style="color:var(--danger)">error</span></div>
      <div class="stat-label">{{ __('activity.stats.error_rate') }}</div>
      <div class="stat-value">{{ number_format($stats->errors) }}</div>
    </div>
  </div>

  <!-- Chart + Top models -->
  <div class="act-layout">
    <div class="dash-card">
      <div class="dash-card-title">{{ __('activity.chart_title') }}</div>
      <div class="chart-wrap"><canvas id="usageChart"></canvas></div>
    </div>

    <div class="dash-card">
      <div class="dash-card-title">{{ __('activity.top_models') }}</div>
      @if($topModels->count())
        <div class="top-list">
          @foreach($topModels as $i => $m)
            <a href="{{ route('models.show', $m->model) }}" class="top-item">
              <div class="top-rank">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
              <div class="top-info">
                <div class="top-name">{{ $m->model }}</div>
                <div class="top-meta">{{ number_format($m->cnt) }} {{ __('common.no_data') === 'No data yet' ? 'requests' : (app()->getLocale() === 'uz' ? "so'rov" : 'запросов') }}</div>
              </div>
              <div class="top-cost">{{ number_format($m->cost, 0, '.', ' ') }}</div>
            </a>
          @endforeach
        </div>
      @else
        <div class="empty">
          <span class="material-icons-round">bar_chart</span>
          <p>{{ __('activity.no_data') }}</p>
        </div>
      @endif
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('usageChart');
if (ctx) {
  const isDark = document.documentElement.dataset.theme === 'dark';
  const textColor = isDark ? '#B0B0B8' : '#6B7280';
  const gridColor = isDark ? '#2A2A2A' : '#E5E7EB';
  const lineColor = isDark ? '#FFFFFF' : '#111111';

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: @json($chartLabels),
      datasets: [{
        label: 'Requests',
        data: @json($chartRequests),
        borderColor: lineColor,
        backgroundColor: isDark ? 'rgba(255,255,255,.06)' : 'rgba(17,17,17,.04)',
        borderWidth: 2,
        fill: true,
        tension: 0.35,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointBackgroundColor: lineColor,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } }, border: { display: false } },
        y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } }, border: { display: false }, beginAtZero: true }
      }
    }
  });
}
</script>
@endsection