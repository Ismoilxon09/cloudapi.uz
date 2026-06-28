@extends('layouts.app')

@section('title', __('dashboard.title') . ' — CloudAPI')

@push('styles')
<style>
.dashboard-wrap {
  max-width: 1400px;
  margin: 0 auto;
  padding: 32px 24px;
  position: relative;
  z-index: 2;
}

/* Header */
.dash-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 28px;
  flex-wrap: wrap;
}

.dash-greeting {
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.dash-subtitle {
  font-size: 14px;
  color: var(--text-muted);
}

.dash-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

/* Alert */
.alert-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: rgba(245, 158, 11, .08);
  border: 1px solid rgba(245, 158, 11, .25);
  border-radius: 12px;
  font-size: 13px;
  color: var(--warning);
  margin-bottom: 24px;
}

.alert-bar.danger {
  background: rgba(239, 68, 68, .08);
  border-color: rgba(239, 68, 68, .25);
  color: var(--danger);
}

.alert-bar .material-icons-round { font-size: 18px; flex-shrink: 0; }

.alert-bar-content { flex: 1; }

.alert-bar a {
  text-decoration: underline;
  font-weight: 600;
}

/* Getting started progress */
.getting-started {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 28px;
  position: relative;
  overflow: hidden;
}

.gs-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.gs-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-strong);
}

.gs-progress {
  font-size: 12px;
  color: var(--text-muted);
  font-weight: 600;
  font-family: 'JetBrains Mono', monospace;
}

.gs-progress-bar {
  height: 4px;
  background: var(--bg-subtle);
  border-radius: 99px;
  overflow: hidden;
  margin-bottom: 20px;
}

.gs-progress-fill {
  height: 100%;
  background: var(--text-strong);
  border-radius: 99px;
  transition: width .6s var(--ease-spring);
}

.gs-steps {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}

.gs-step {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 10px;
  transition: all .15s;
}

.gs-step.done {
  background: var(--bg-elevated);
  border-color: var(--success);
}

.gs-step-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--gray-deep);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 13px;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
}

.gs-step.done .gs-step-icon {
  background: var(--success);
}

.gs-step-content { flex: 1; min-width: 0; }

.gs-step-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-strong);
  margin-bottom: 2px;
}

.gs-step-desc {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.5;
}

.gs-step.done .gs-step-title { color: var(--success); }

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}

.stat-tile {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 18px;
  transition: all .15s;
}

.stat-tile:hover {
  border-color: var(--border-strong);
  box-shadow: var(--shadow-sm);
}

.stat-tile-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}

.stat-tile-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.stat-tile-icon {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  background: var(--bg-subtle);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
}

.stat-tile-icon .material-icons-round { font-size: 16px; }

.stat-tile-value {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 2px;
}

.stat-tile-meta {
  font-size: 11px;
  color: var(--text-muted);
}

.stat-tile-currency {
  font-size: 14px;
  color: var(--text-muted);
  font-weight: 500;
  margin-left: 2px;
}

/* Two-column layout */
.dash-layout {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 16px;
  margin-bottom: 24px;
}

.dash-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 20px;
}

.dash-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.dash-card-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-strong);
}

.dash-card-subtitle {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 2px;
}

.dash-card-action {
  font-size: 12px;
  color: var(--text-muted);
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: color .15s;
}

.dash-card-action:hover { color: var(--text-strong); }
.dash-card-action .material-icons-round { font-size: 14px; }

/* Chart */
.chart-container {
  position: relative;
  height: 260px;
}

.chart-tabs {
  display: flex;
  gap: 4px;
  background: var(--bg-subtle);
  padding: 3px;
  border-radius: 7px;
}

.chart-tab {
  padding: 5px 10px;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-muted);
  border-radius: 5px;
  cursor: pointer;
}

.chart-tab.active {
  background: var(--bg-elevated);
  color: var(--text-strong);
  box-shadow: var(--shadow-sm);
}

/* Top models list */
.top-models-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.top-model-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: var(--bg-subtle);
  border-radius: 8px;
  transition: all .15s;
  text-decoration: none;
  color: inherit;
}

.top-model-item:hover {
  background: var(--bg-hover);
}

.top-model-rank {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  background: var(--gray-deep);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  flex-shrink: 0;
}

.top-model-info { flex: 1; min-width: 0; }

.top-model-name {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-strong);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.top-model-meta {
  font-size: 10px;
  color: var(--text-muted);
  margin-top: 1px;
}

.top-model-count {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-strong);
  font-family: 'JetBrains Mono', monospace;
}

/* Activity table */
.activity-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}

.activity-table th {
  text-align: left;
  font-size: 10px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 8px 12px;
  border-bottom: 1px solid var(--border);
}

.activity-table td {
  padding: 10px 12px;
  border-bottom: 1px solid var(--border);
}

.activity-table tr:last-child td { border-bottom: none; }
.activity-table tr:hover td { background: var(--bg-subtle); }

.activity-model {
  font-family: 'JetBrains Mono', monospace;
  font-size: 11px;
  color: var(--text-strong);
}

.activity-tokens {
  font-family: 'JetBrains Mono', monospace;
  color: var(--text-muted);
}

.activity-cost {
  font-weight: 600;
  color: var(--text-strong);
  font-family: 'JetBrains Mono', monospace;
}

.activity-time {
  color: var(--text-muted);
  font-size: 11px;
}

.activity-status {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 99px;
}

.activity-status.success {
  background: rgba(16, 185, 129, .1);
  color: var(--success);
}

.activity-status.error {
  background: rgba(239, 68, 68, .1);
  color: var(--danger);
}

/* Empty state */
.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-muted);
}

.empty-state .material-icons-round {
  font-size: 40px;
  color: var(--text-subtle);
  margin-bottom: 12px;
  opacity: 0.6;
}

.empty-state h3 {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.empty-state p {
  font-size: 12px;
  line-height: 1.5;
  max-width: 240px;
  margin: 0 auto;
}

/* Quick actions */
.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px;
  margin-bottom: 24px;
}

.quick-action {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  text-decoration: none;
  color: inherit;
  transition: all .15s;
}

.quick-action:hover {
  border-color: var(--text-muted);
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.quick-action-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: var(--gray-deep);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.quick-action-icon .material-icons-round { font-size: 18px; }

.quick-action-text {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-strong);
}

.quick-action-arrow {
  margin-left: auto;
  color: var(--text-subtle);
}

@media (max-width: 1024px) {
  .dash-layout { grid-template-columns: 1fr; }
  .gs-steps { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

<div class="dashboard-wrap">
  <!-- Header -->
  <div class="dash-header">
    <div>
      <h1 class="dash-greeting">{{ __('dashboard.welcome') }}, {{ $user->name }}</h1>
      <p class="dash-subtitle">{{ __('dashboard.subtitle') }}</p>
    </div>
  </div>

  <!-- Alerts -->
  @if($lowBalance)
  <div class="alert-bar danger">
    <span class="material-icons-round">warning</span>
    <div class="alert-bar-content">
      {{ __('dashboard.low_balance_warning') }}
    </div>
    <a href="{{ route('billing.topup') }}" class="btn btn-sm btn-secondary" style="text-decoration:none">
      {{ __('dashboard.quick_actions.topup') }}
    </a>
  </div>
  @endif

  @if(!$hasKey)
  <div class="alert-bar">
    <span class="material-icons-round">info</span>
    <div class="alert-bar-content">
      {{ __('dashboard.no_keys_warning') }}
    </div>
    <a href="{{ route('keys.index') }}" class="btn btn-sm btn-secondary" style="text-decoration:none">
      {{ __('dashboard.quick_actions.new_key') }}
    </a>
  </div>
  @endif

  <!-- Getting Started -->
  @if($progress < 100)
  <div class="getting-started">
    <div class="gs-header">
      <div>
        <div class="gs-title">{{ __('dashboard.getting_started.title') }}</div>
      </div>
      <div class="gs-progress">{{ $progress }}%</div>
    </div>
    <div class="gs-progress-bar">
      <div class="gs-progress-fill" style="width: {{ $progress }}%"></div>
    </div>
    <div class="gs-steps">
      <a href="{{ route('keys.index') }}" class="gs-step {{ $hasKey ? 'done' : '' }}" style="text-decoration:none;color:inherit">
        <div class="gs-step-icon">{{ $hasKey ? '✓' : '01' }}</div>
        <div class="gs-step-content">
          <div class="gs-step-title">{{ __('dashboard.getting_started.step_1') }}</div>
          <div class="gs-step-desc">{{ __('dashboard.getting_started.step_1_desc') }}</div>
        </div>
      </a>
      <a href="{{ route('billing.topup') }}" class="gs-step {{ $hasBalance ? 'done' : '' }}" style="text-decoration:none;color:inherit">
        <div class="gs-step-icon">{{ $hasBalance ? '✓' : '02' }}</div>
        <div class="gs-step-content">
          <div class="gs-step-title">{{ __('dashboard.getting_started.step_2') }}</div>
          <div class="gs-step-desc">{{ __('dashboard.getting_started.step_2_desc') }}</div>
        </div>
      </a>
      <a href="{{ route('playground.index') }}" class="gs-step {{ $hasRequest ? 'done' : '' }}" style="text-decoration:none;color:inherit">
        <div class="gs-step-icon">{{ $hasRequest ? '✓' : '03' }}</div>
        <div class="gs-step-content">
          <div class="gs-step-title">{{ __('dashboard.getting_started.step_3') }}</div>
          <div class="gs-step-desc">{{ __('dashboard.getting_started.step_3_desc') }}</div>
        </div>
      </a>
    </div>
  </div>
  @endif

  <!-- Stats Grid -->
  <div class="stats-grid">
    <!-- Balance -->
    <div class="stat-tile">
      <div class="stat-tile-header">
        <div class="stat-tile-label">{{ __('dashboard.stats.balance') }}</div>
        <div class="stat-tile-icon"><span class="material-icons-round">account_balance_wallet</span></div>
      </div>
      <div class="stat-tile-value">
        {{ number_format($balance, 0, '.', ' ') }}<span class="stat-tile-currency">{{ __('common.currency') }}</span>
      </div>
      <div class="stat-tile-meta">
        @if($balance > 0)
          <a href="{{ route('billing.index') }}" style="color:var(--accent);text-decoration:none">{{ __('common.view_all') }} →</a>
        @else
          <a href="{{ route('billing.topup') }}" style="color:var(--accent);text-decoration:none">{{ __('dashboard.quick_actions.topup') }} →</a>
        @endif
      </div>
    </div>

    <!-- Requests Today -->
    <div class="stat-tile">
      <div class="stat-tile-header">
        <div class="stat-tile-label">{{ __('dashboard.stats.requests_today') }}</div>
        <div class="stat-tile-icon"><span class="material-icons-round">today</span></div>
      </div>
      <div class="stat-tile-value">{{ number_format($requestsToday) }}</div>
      <div class="stat-tile-meta">{{ number_format($requestsMonth) }} {{ app()->getLocale() === 'uz' ? 'bu oyda' : (app()->getLocale() === 'ru' ? 'в этом месяце' : 'this month') }}</div>
    </div>

    <!-- Tokens this month -->
    <div class="stat-tile">
      <div class="stat-tile-header">
        <div class="stat-tile-label">{{ __('dashboard.stats.tokens_month') }}</div>
        <div class="stat-tile-icon"><span class="material-icons-round">data_usage</span></div>
      </div>
      <div class="stat-tile-value">
        @if($tokensMonth >= 1000000)
          {{ number_format($tokensMonth / 1000000, 2) }}<span class="stat-tile-currency">M</span>
        @elseif($tokensMonth >= 1000)
          {{ number_format($tokensMonth / 1000, 1) }}<span class="stat-tile-currency">K</span>
        @else
          {{ number_format($tokensMonth) }}
        @endif
      </div>
      <div class="stat-tile-meta">{{ number_format($spentMonth, 0, '.', ' ') }} {{ __('common.currency') }} {{ app()->getLocale() === 'uz' ? 'sarflandi' : (app()->getLocale() === 'ru' ? 'потрачено' : 'spent') }}</div>
    </div>

    <!-- Active keys -->
    <div class="stat-tile">
      <div class="stat-tile-header">
        <div class="stat-tile-label">{{ __('dashboard.stats.active_keys') }}</div>
        <div class="stat-tile-icon"><span class="material-icons-round">key</span></div>
      </div>
      <div class="stat-tile-value">{{ $activeKeysCount }}<span class="stat-tile-currency">/{{ $totalKeysCount }}</span></div>
      <div class="stat-tile-meta">
        <a href="{{ route('keys.index') }}" style="color:var(--accent);text-decoration:none">{{ __('common.view_all') }} →</a>
      </div>
    </div>
  </div>

  <!-- Two-column: Chart + Top Models -->
  <div class="dash-layout">
    <!-- Chart -->
    <div class="dash-card">
      <div class="dash-card-header">
        <div>
          <div class="dash-card-title">{{ __('dashboard.chart.title') }}</div>
          <div class="dash-card-subtitle">{{ __('dashboard.chart.last_7') }}</div>
        </div>
      </div>
      <div class="chart-container">
        <canvas id="usageChart"></canvas>
      </div>
    </div>

    <!-- Top Models -->
    <div class="dash-card">
      <div class="dash-card-header">
        <div>
          <div class="dash-card-title">{{ __('dashboard.top_models.title') }}</div>
          <div class="dash-card-subtitle">{{ __('dashboard.top_models.subtitle') }}</div>
        </div>
        <a href="{{ route('models.index') }}" class="dash-card-action">
          {{ __('common.view_all') }}
          <span class="material-icons-round">arrow_forward</span>
        </a>
      </div>

      @if($topModels->count())
        <div class="top-models-list">
          @foreach($topModels as $i => $tm)
            @php
              $provider = explode('/', $tm->model)[0];
              $displayName = explode('/', $tm->model)[1] ?? $tm->model;
            @endphp
            <a href="{{ route('models.show', $tm->model) }}" class="top-model-item">
              <div class="top-model-rank">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
              <div class="top-model-info">
                <div class="top-model-name">{{ $displayName }}</div>
                <div class="top-model-meta">{{ $provider }}</div>
              </div>
              <div class="top-model-count">{{ $tm->cnt }}</div>
            </a>
          @endforeach
        </div>
      @else
        <div class="empty-state">
          <span class="material-icons-round">bar_chart</span>
          <h3>{{ __('dashboard.top_models.no_data') }}</h3>
        </div>
      @endif
    </div>
  </div>

  <!-- Quick Actions -->
  <div style="margin-bottom: 12px">
    <div style="font-size: 11px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px">
      {{ __('dashboard.quick_actions.title') }}
    </div>
    <div class="quick-actions-grid">
      <a href="{{ route('keys.index') }}" class="quick-action">
        <div class="quick-action-icon"><span class="material-icons-round">key</span></div>
        <div class="quick-action-text">{{ __('dashboard.quick_actions.new_key') }}</div>
        <span class="material-icons-round quick-action-arrow">arrow_forward</span>
      </a>
      <a href="{{ route('billing.topup') }}" class="quick-action">
        <div class="quick-action-icon"><span class="material-icons-round">payments</span></div>
        <div class="quick-action-text">{{ __('dashboard.quick_actions.topup') }}</div>
        <span class="material-icons-round quick-action-arrow">arrow_forward</span>
      </a>
      <a href="{{ route('playground.index') }}" class="quick-action">
        <div class="quick-action-icon"><span class="material-icons-round">play_arrow</span></div>
        <div class="quick-action-text">{{ __('dashboard.quick_actions.playground') }}</div>
        <span class="material-icons-round quick-action-arrow">arrow_forward</span>
      </a>
      <a href="{{ route('models.index') }}" class="quick-action">
        <div class="quick-action-icon"><span class="material-icons-round">memory</span></div>
        <div class="quick-action-text">{{ __('dashboard.quick_actions.browse_models') }}</div>
        <span class="material-icons-round quick-action-arrow">arrow_forward</span>
      </a>
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="dash-card">
    <div class="dash-card-header">
      <div>
        <div class="dash-card-title">{{ __('dashboard.recent_activity.title') }}</div>
      </div>
      <a href="{{ route('billing.index') }}" class="dash-card-action">
        {{ __('dashboard.recent_activity.view_all') }}
        <span class="material-icons-round">arrow_forward</span>
      </a>
    </div>

    @if($recentUsage->count())
      <div style="overflow-x: auto">
        <table class="activity-table">
          <thead>
            <tr>
              <th>{{ __('dashboard.recent_activity.model') }}</th>
              <th>{{ __('dashboard.recent_activity.tokens') }}</th>
              <th>{{ __('dashboard.recent_activity.cost') }}</th>
              <th>{{ __('common.status') }}</th>
              <th>{{ __('dashboard.recent_activity.time') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentUsage as $u)
              <tr>
                <td>
                  <span class="activity-model">{{ $u->model }}</span>
                </td>
                <td>
                  <span class="activity-tokens">{{ number_format($u->tokens_in) }} → {{ number_format($u->tokens_out) }}</span>
                </td>
                <td>
                  <span class="activity-cost">{{ number_format($u->cost_uzs, 2) }} {{ __('common.currency') }}</span>
                </td>
                <td>
                  @if($u->status_code >= 200 && $u->status_code < 300)
                    <span class="activity-status success">
                      <span class="material-icons-round" style="font-size:10px">check_circle</span>
                      {{ $u->status_code }}
                    </span>
                  @else
                    <span class="activity-status error">
                      <span class="material-icons-round" style="font-size:10px">error</span>
                      {{ $u->status_code }}
                    </span>
                  @endif
                </td>
                <td>
                  <span class="activity-time" title="{{ $u->created_at }}">{{ $u->created_at->diffForHumans() }}</span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="empty-state">
        <span class="material-icons-round">history</span>
        <h3>{{ __('dashboard.recent_activity.no_activity') }}</h3>
        <p>{{ __('dashboard.recent_activity.no_activity_desc') }}</p>
      </div>
    @endif
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('usageChart');
if (ctx) {
  const isDark = document.documentElement.dataset.theme === 'dark';
  const textColor = isDark ? '#9CA3AF' : '#6B7280';
  const gridColor = isDark ? '#262626' : '#E5E7EB';
  const lineColor = isDark ? '#FFFFFF' : '#111111';

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: @json($chartLabels),
      datasets: [{
        label: '{{ __("dashboard.chart.requests") }}',
        data: @json($chartRequests),
        borderColor: lineColor,
        backgroundColor: isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(17, 17, 17, 0.04)',
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
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: isDark ? '#111111' : '#FFFFFF',
          titleColor: isDark ? '#FFFFFF' : '#111111',
          bodyColor: textColor,
          borderColor: gridColor,
          borderWidth: 1,
          padding: 10,
          cornerRadius: 8,
          displayColors: false,
          titleFont: { size: 12, weight: 600 },
          bodyFont: { size: 12 },
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: textColor, font: { size: 11 } },
          border: { display: false },
        },
        y: {
          grid: { color: gridColor, drawBorder: false },
          ticks: { color: textColor, font: { size: 11 }, padding: 8 },
          border: { display: false },
          beginAtZero: true,
        }
      }
    }
  });
}
</script>
@endpush