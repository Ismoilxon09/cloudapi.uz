@extends('admin.layout')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<style>
.adm-page { padding: 24px; max-width: 1600px; margin: 0 auto; }

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 14px;
  margin-bottom: 24px;
}

.kpi-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 18px;
  position: relative;
}

.kpi-card.alert {
  border-color: var(--warning);
  background: linear-gradient(135deg, rgba(245,158,11,.08) 0%, transparent 100%);
}

.kpi-card.primary {
  background: var(--primary);
  color: var(--bg-elevated);
  border-color: var(--primary);
}

.kpi-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--bg-subtle);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  float: right;
}

.kpi-card.primary .kpi-icon { background: rgba(255,255,255,.12); }
.kpi-icon .material-icons-round { font-size: 16px; color: var(--text-muted); }
.kpi-card.primary .kpi-icon .material-icons-round { color: rgba(255,255,255,.9); }

.kpi-label {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 8px;
}

.kpi-card.primary .kpi-label { color: rgba(255,255,255,.65); }

.kpi-value {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.kpi-card.primary .kpi-value { color: white; }

.kpi-value .meta {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
  margin-left: 3px;
}

.kpi-card.primary .kpi-value .meta { color: rgba(255,255,255,.7); }

.kpi-trend {
  font-size: 11px;
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-weight: 600;
}

.kpi-trend.up { color: var(--success); }
.kpi-trend.down { color: var(--danger); }
.kpi-trend.flat { color: var(--text-muted); }
.kpi-trend .material-icons-round { font-size: 12px; }

.kpi-meta {
  font-size: 11px;
  color: var(--text-muted);
}

.kpi-link {
  position: absolute;
  inset: 0;
  z-index: 1;
}

/* Two-column layout */
.adm-layout {
  display: grid;
  grid-template-columns: 1.7fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

@media (max-width: 1100px) { .adm-layout { grid-template-columns: 1fr; } }

.adm-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 20px;
}

.adm-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.adm-card-title { font-size: 14px; font-weight: 700; color: var(--text-strong); }

.adm-card-link {
  font-size: 12px;
  color: var(--text-muted);
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.adm-card-link:hover { color: var(--text-strong); }
.adm-card-link .material-icons-round { font-size: 14px; }

/* Chart */
.chart-wrap { height: 280px; }

/* Lists */
.list-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: var(--bg-subtle);
  border-radius: 8px;
  margin-bottom: 6px;
  text-decoration: none;
  color: inherit;
}

.list-item:hover { background: var(--bg-hover); }

.list-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: var(--primary);
  color: var(--bg-elevated);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
  flex-shrink: 0;
}

.list-info { flex: 1; min-width: 0; }

.list-name {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-strong);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.list-meta {
  font-size: 10px;
  color: var(--text-muted);
  margin-top: 1px;
}

.list-value {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-strong);
  font-family: 'JetBrains Mono', monospace;
}

.list-empty {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-muted);
  font-size: 13px;
}

.list-empty .material-icons-round {
  font-size: 32px;
  color: var(--text-subtle);
  margin-bottom: 8px;
}

/* Pending row */
.pending-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: var(--bg-subtle);
  border-radius: 8px;
  border-left: 3px solid var(--warning);
  margin-bottom: 6px;
}

.pending-amount {
  font-size: 13px;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  color: var(--warning);
}

/* Notification item */
.notif-item {
  display: flex;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  margin-bottom: 4px;
  text-decoration: none;
  color: inherit;
  border-left: 3px solid transparent;
}

.notif-item.unread { background: var(--bg-subtle); }
.notif-item:hover { background: var(--bg-hover); }

.notif-item.priority-urgent { border-left-color: var(--danger); }
.notif-item.priority-high { border-left-color: var(--warning); }
.notif-item.priority-normal { border-left-color: var(--accent); }

.notif-content { flex: 1; min-width: 0; }
.notif-title { font-size: 12px; font-weight: 600; color: var(--text-strong); }
.notif-message { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.notif-time { font-size: 10px; color: var(--text-subtle); margin-top: 4px; }
</style>
@endpush

@section('content')
<div class="adm-page">
  @if($pendingPayments > 0)
    <div class="alert alert-info" style="background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.3);color:var(--warning)">
      <span class="material-icons-round">notifications_active</span>
      <div style="flex:1">
        <strong>{{ $pendingPayments }}</strong> ta to'lov sizning tasdiqlashingizni kutmoqda
        ({{ number_format($pendingAmount, 0, '.', ' ') }} so'm)
      </div>
      <a href="{{ route('admin.payments.index') }}" class="btn btn-warning btn-sm" style="background:var(--warning);color:white">
        Ko'rish
        <span class="material-icons-round">arrow_forward</span>
      </a>
    </div>
  @endif

  <!-- KPI Cards -->
  <div class="kpi-grid">
    <!-- Bugungi daromad -->
    <div class="kpi-card primary">
      <a href="{{ route('admin.stats.revenue') }}" class="kpi-link"></a>
      <div class="kpi-icon"><span class="material-icons-round">payments</span></div>
      <div class="kpi-label">Bugungi daromad</div>
      <div class="kpi-value">{{ number_format($todayRevenue, 0, '.', ' ') }}<span class="meta">so'm</span></div>
      <div class="kpi-meta" style="color:rgba(255,255,255,.7)">Bu oyda: {{ number_format($monthRevenue, 0, '.', ' ') }} so'm</div>
    </div>

    <!-- Pending to'lovlar -->
    <div class="kpi-card {{ $pendingPayments > 0 ? 'alert' : '' }}">
      <a href="{{ route('admin.payments.index') }}" class="kpi-link"></a>
      <div class="kpi-icon" style="{{ $pendingPayments > 0 ? 'background:rgba(245,158,11,.15)' : '' }}">
        <span class="material-icons-round" style="{{ $pendingPayments > 0 ? 'color:var(--warning)' : '' }}">hourglass_top</span>
      </div>
      <div class="kpi-label">Pending to'lovlar</div>
      <div class="kpi-value">{{ $pendingPayments }}</div>
      <div class="kpi-meta">{{ number_format($pendingAmount, 0, '.', ' ') }} so'm jami</div>
    </div>

    <!-- Foydalanuvchilar -->
    <div class="kpi-card">
      <a href="{{ route('admin.users.index') }}" class="kpi-link"></a>
      <div class="kpi-icon"><span class="material-icons-round">group</span></div>
      <div class="kpi-label">Foydalanuvchilar</div>
      <div class="kpi-value">{{ number_format($totalUsers) }}</div>
      <div class="kpi-meta">
        +{{ $newUsersToday }} bugun
        @if($userGrowth != 0)
          <span class="kpi-trend {{ $userGrowth > 0 ? 'up' : 'down' }}">
            <span class="material-icons-round">{{ $userGrowth > 0 ? 'arrow_upward' : 'arrow_downward' }}</span>
            {{ abs($userGrowth) }}%
          </span>
        @endif
      </div>
    </div>

    <!-- Faol bugun -->
    <div class="kpi-card">
      <div class="kpi-icon"><span class="material-icons-round">trending_up</span></div>
      <div class="kpi-label">Bugun faol</div>
      <div class="kpi-value">{{ $activeToday }}</div>
      <div class="kpi-meta">noyob foydalanuvchi</div>
    </div>

    <!-- So'rovlar -->
    <div class="kpi-card">
      <a href="{{ route('admin.logs.index') }}" class="kpi-link"></a>
      <div class="kpi-icon"><span class="material-icons-round">send</span></div>
      <div class="kpi-label">Bugungi so'rovlar</div>
      <div class="kpi-value">{{ number_format($requestsToday) }}</div>
      <div class="kpi-meta">
        Bu oyda: {{ number_format($requestsMonth) }}
        @if($requestGrowth != 0)
          <span class="kpi-trend {{ $requestGrowth > 0 ? 'up' : 'down' }}">
            <span class="material-icons-round">{{ $requestGrowth > 0 ? 'arrow_upward' : 'arrow_downward' }}</span>
            {{ abs($requestGrowth) }}%
          </span>
        @endif
      </div>
    </div>

    <!-- Tokenlar -->
    <div class="kpi-card">
      <div class="kpi-icon"><span class="material-icons-round">data_usage</span></div>
      <div class="kpi-label">Bugun tokenlar</div>
      <div class="kpi-value">
        @if($tokensToday >= 1000000)
          {{ number_format($tokensToday / 1000000, 1) }}<span class="meta">M</span>
        @elseif($tokensToday >= 1000)
          {{ number_format($tokensToday / 1000, 0) }}<span class="meta">K</span>
        @else
          {{ number_format($tokensToday) }}
        @endif
      </div>
      <div class="kpi-meta">
        @if($tokensMonth >= 1000000)
          Oy: {{ number_format($tokensMonth / 1000000, 1) }}M
        @else
          Oy: {{ number_format($tokensMonth) }}
        @endif
      </div>
    </div>

    <!-- Modellar -->
    <div class="kpi-card">
      <a href="{{ route('admin.models.index') }}" class="kpi-link"></a>
      <div class="kpi-icon"><span class="material-icons-round">memory</span></div>
      <div class="kpi-label">Modellar</div>
      <div class="kpi-value">{{ $totalModels }}</div>
      <div class="kpi-meta">{{ $activeModels }} faol</div>
    </div>

    <!-- API kalitlar -->
    <div class="kpi-card">
      <a href="{{ route('admin.keys.index') }}" class="kpi-link"></a>
      <div class="kpi-icon"><span class="material-icons-round">key</span></div>
      <div class="kpi-label">API kalitlar</div>
      <div class="kpi-value">{{ $totalKeys }}</div>
      <div class="kpi-meta">{{ $activeKeys }} faol</div>
    </div>
  </div>

  <!-- Chart + Notifications -->
  <div class="adm-layout">
    <div class="adm-card">
      <div class="adm-card-header">
        <div class="adm-card-title">Daromad va so'rovlar — oxirgi 14 kun</div>
      </div>
      <div class="chart-wrap"><canvas id="mainChart"></canvas></div>
    </div>

    <div class="adm-card">
      <div class="adm-card-header">
        <div class="adm-card-title">Bildirishnomalar</div>
        <a href="{{ route('admin.notifications.index') }}" class="adm-card-link">
          Hammasi
          <span class="material-icons-round">arrow_forward</span>
        </a>
      </div>

      @if($notifications->isEmpty())
        <div class="list-empty">
          <span class="material-icons-round">notifications_off</span>
          <p>Bildirishnomalar yo'q</p>
        </div>
      @else
        @foreach($notifications as $n)
        <a href="{{ $n->target_url ?? '#' }}" class="notif-item {{ !$n->read_at ? 'unread' : '' }} priority-{{ $n->priority }}">
          <div class="notif-content">
            <div class="notif-title">{{ $n->title }}</div>
            @if($n->message)
              <div class="notif-message">{{ \Illuminate\Support\Str::limit($n->message, 80) }}</div>
            @endif
            <div class="notif-time">{{ $n->created_at->diffForHumans() }}</div>
          </div>
        </a>
        @endforeach
      @endif
    </div>
  </div>

  <!-- Pending + Top users -->
  <div class="adm-layout">
    <div class="adm-card">
      <div class="adm-card-header">
        <div class="adm-card-title">Pending to'lovlar</div>
        <a href="{{ route('admin.payments.index') }}" class="adm-card-link">
          Hammasi
          <span class="material-icons-round">arrow_forward</span>
        </a>
      </div>

      @if($pendingList->isEmpty())
        <div class="list-empty">
          <span class="material-icons-round">check_circle</span>
          <p>Pending to'lovlar yo'q</p>
        </div>
      @else
        @foreach($pendingList as $tx)
        <a href="{{ route('admin.payments.show', $tx) }}" class="list-item">
          <div class="list-avatar">{{ strtoupper(substr($tx->user->name ?? '?', 0, 1)) }}</div>
          <div class="list-info">
            <div class="list-name">{{ $tx->user->name ?? 'Unknown' }}</div>
            <div class="list-meta">{{ $tx->created_at->diffForHumans() }}</div>
          </div>
          <div class="pending-amount">+{{ number_format($tx->amount_uzs, 0, '.', ' ') }}</div>
        </a>
        @endforeach
      @endif
    </div>

    <div class="adm-card">
      <div class="adm-card-header">
        <div class="adm-card-title">Top foydalanuvchilar (bu oyda)</div>
        <a href="{{ route('admin.users.index') }}" class="adm-card-link">
          Hammasi
          <span class="material-icons-round">arrow_forward</span>
        </a>
      </div>

      @if($topUsers->isEmpty() || $topUsers->first()?->month_spent == 0)
        <div class="list-empty">
          <span class="material-icons-round">people_outline</span>
          <p>Hozircha faollik yo'q</p>
        </div>
      @else
        @foreach($topUsers as $u)
          @if($u->month_spent > 0)
          <a href="{{ route('admin.users.show', $u) }}" class="list-item">
            <div class="list-avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
            <div class="list-info">
              <div class="list-name">{{ $u->name }}</div>
              <div class="list-meta">{{ $u->email }}</div>
            </div>
            <div class="list-value">{{ number_format($u->month_spent, 0, '.', ' ') }}</div>
          </a>
          @endif
        @endforeach
      @endif
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('mainChart');
if (ctx) {
  const isDark = document.documentElement.dataset.theme === 'dark';
  const textColor = isDark ? '#B0B0B8' : '#64748B';
  const gridColor = isDark ? '#2A2A2A' : '#E2E8F0';

  const data = @json($chartData);

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(d => d.date),
      datasets: [
        {
          label: 'Daromad (so\'m)',
          data: data.map(d => d.revenue),
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, .08)',
          borderWidth: 2,
          fill: true,
          tension: 0.35,
          yAxisID: 'y',
        },
        {
          label: 'So\'rovlar',
          data: data.map(d => d.requests),
          borderColor: '#2563EB',
          backgroundColor: 'transparent',
          borderWidth: 2,
          tension: 0.35,
          yAxisID: 'y1',
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: textColor, usePointStyle: true, padding: 16, font: { size: 11 } },
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } }, border: { display: false } },
        y: {
          position: 'left',
          grid: { color: gridColor },
          ticks: { color: textColor, font: { size: 11 } },
          border: { display: false },
          beginAtZero: true,
        },
        y1: {
          position: 'right',
          grid: { display: false },
          ticks: { color: textColor, font: { size: 11 } },
          border: { display: false },
          beginAtZero: true,
        }
      }
    }
  });
}
</script>
@endsection