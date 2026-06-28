@extends('admin.layout')

@section('title', 'Statistika')
@section('page_title', 'Statistika')

@push('styles')
<style>
.stats-page { padding: 24px; max-width: 1600px; margin: 0 auto; }

.range-tabs { display: flex; gap: 4px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 10px; padding: 3px; }
.range-tab { padding: 6px 12px; font-size: 12px; font-weight: 600; color: var(--text-muted); border-radius: 6px; }
.range-tab:hover { color: var(--text-strong); }
.range-tab.active { background: var(--text-strong); color: var(--bg); }

.big-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
@media (max-width: 1000px) { .big-stats { grid-template-columns: repeat(2, 1fr); } }

.big-stat {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 20px;
}

.big-stat.revenue { background: var(--primary); color: var(--bg-elevated); border-color: var(--primary); }
.big-stat.profit { background: linear-gradient(135deg, var(--success) 0%, #059669 100%); color: white; border-color: var(--success); }

.bs-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-subtle); margin-bottom: 8px; }
.big-stat.revenue .bs-label, .big-stat.profit .bs-label { color: rgba(255,255,255,.7); }

.bs-value { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-strong); font-family: 'JetBrains Mono', monospace; }
.big-stat.revenue .bs-value, .big-stat.profit .bs-value { color: white; }
.bs-value .currency { font-size: 13px; font-weight: 600; opacity: 0.7; margin-left: 3px; font-family: 'Inter', sans-serif; }

.bs-meta { font-size: 11px; color: var(--text-muted); margin-top: 6px; }
.big-stat.revenue .bs-meta, .big-stat.profit .bs-meta { color: rgba(255,255,255,.7); }

.adm-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 14px; padding: 22px; margin-bottom: 16px; }
.adm-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
.adm-card-title { font-size: 14px; font-weight: 700; color: var(--text-strong); }

.chart-wrap { height: 320px; }

.layout-split { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 900px) { .layout-split { grid-template-columns: 1fr; } }

table { width: 100%; border-collapse: collapse; font-size: 12px; }
th { text-align: left; font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; padding: 8px 10px; border-bottom: 1px solid var(--border); }
td { padding: 10px; border-bottom: 1px solid var(--border); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }

.rank { width: 22px; height: 22px; border-radius: 5px; background: var(--gray-deep); color: var(--bg); display: inline-flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
[data-theme="dark"] .rank { background: var(--text-strong); color: var(--bg); }
.tbl-name { font-weight: 600; color: var(--text-strong); }
.tbl-meta { font-size: 10px; color: var(--text-muted); }
.tbl-value { font-family: 'JetBrains Mono', monospace; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="stats-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">Statistika</h1>
      <p class="page-subtitle">Daromad, foyda va foydalanish trendlari</p>
    </div>
    <div class="range-tabs">
      <a href="?range=7d" class="range-tab {{ $range === '7d' ? 'active' : '' }}">7 kun</a>
      <a href="?range=30d" class="range-tab {{ $range === '30d' ? 'active' : '' }}">30 kun</a>
      <a href="?range=90d" class="range-tab {{ $range === '90d' ? 'active' : '' }}">90 kun</a>
      <a href="?range=365d" class="range-tab {{ $range === '365d' ? 'active' : '' }}">1 yil</a>
    </div>
  </div>

  <!-- Big stats -->
  <div class="big-stats">
    <div class="big-stat revenue">
      <div class="bs-label">Jami daromad</div>
      <div class="bs-value">{{ number_format($totalRevenue, 0, '.', ' ') }}<span class="currency">so'm</span></div>
      <div class="bs-meta">User'lardan tushgan to'lovlar</div>
    </div>

    <div class="big-stat profit">
      <div class="bs-label">Sof foyda</div>
      <div class="bs-value">{{ number_format($estimatedProfit, 0, '.', ' ') }}<span class="currency">so'm</span></div>
      <div class="bs-meta">User sarflagan − OpenRouter narxi</div>
    </div>

    <div class="big-stat">
      <div class="bs-label">User sarflagan</div>
      <div class="bs-value">{{ number_format($totalSpent, 0, '.', ' ') }}<span class="currency">so'm</span></div>
      <div class="bs-meta">{{ number_format($totalRequests) }} so'rov</div>
    </div>

    <div class="big-stat">
      <div class="bs-label">OpenRouter ga to'langan</div>
      <div class="bs-value">{{ number_format($totalCostToOpenRouter, 0, '.', ' ') }}<span class="currency">so'm</span></div>
      <div class="bs-meta">Bizning xarajat</div>
    </div>
  </div>

  <!-- Chart -->
  <div class="adm-card">
    <div class="adm-card-header">
      <div class="adm-card-title">Daromad vs Xarajat — oxirgi {{ $days }} kun</div>
    </div>
    <div class="chart-wrap"><canvas id="profitChart"></canvas></div>
  </div>

  <!-- Top models + Top users -->
  <div class="layout-split">
    <div class="adm-card">
      <div class="adm-card-header">
        <div class="adm-card-title">Top modellar (daromad bo'yicha)</div>
      </div>
      @if($topModels->isEmpty())
        <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:13px">Ma'lumot yo'q</div>
      @else
        <table>
          <thead>
            <tr><th>#</th><th>Model</th><th>So'rovlar</th><th>Daromad</th></tr>
          </thead>
          <tbody>
            @foreach($topModels as $i => $m)
            <tr>
              <td><span class="rank">{{ $i + 1 }}</span></td>
              <td>
                <div class="tbl-name">{{ $m->model }}</div>
                <div class="tbl-meta">{{ number_format($m->tokens) }} tokenlar</div>
              </td>
              <td><span class="tbl-meta">{{ number_format($m->cnt) }}</span></td>
              <td><span class="tbl-value">{{ number_format($m->revenue, 0, '.', ' ') }}</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <div class="adm-card">
      <div class="adm-card-header">
        <div class="adm-card-title">Top foydalanuvchilar</div>
      </div>
      @if($topUsers->isEmpty() || ($topUsers->first()?->period_spent ?? 0) == 0)
        <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:13px">Ma'lumot yo'q</div>
      @else
        <table>
          <thead>
            <tr><th>#</th><th>User</th><th>Sarflangan</th></tr>
          </thead>
          <tbody>
            @foreach($topUsers as $i => $u)
              @if($u->period_spent > 0)
              <tr>
                <td><span class="rank">{{ $i + 1 }}</span></td>
                <td>
                  <a href="{{ route('admin.users.show', $u) }}" style="text-decoration:none;color:inherit">
                    <div class="tbl-name">{{ $u->name }}</div>
                    <div class="tbl-meta">{{ $u->email }}</div>
                  </a>
                </td>
                <td><span class="tbl-value">{{ number_format($u->period_spent, 0, '.', ' ') }} so'm</span></td>
              </tr>
              @endif
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('profitChart');
if (ctx) {
  const isDark = document.documentElement.dataset.theme === 'dark';
  const textColor = isDark ? '#B0B0B8' : '#64748B';
  const gridColor = isDark ? '#2A2A2A' : '#E2E8F0';
  const daily = @json($daily);

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: daily.map(d => d.date),
      datasets: [
        {
          label: 'Daromad (so\'m)',
          data: daily.map(d => d.revenue),
          backgroundColor: '#10B981',
          borderRadius: 4,
        },
        {
          label: 'User sarflagan (so\'m)',
          data: daily.map(d => d.spent),
          backgroundColor: isDark ? '#FFFFFF' : '#0F172A',
          borderRadius: 4,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { color: textColor, usePointStyle: true, padding: 16, font: { size: 11 } } }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: textColor, font: { size: 10 } }, border: { display: false } },
        y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 } }, border: { display: false }, beginAtZero: true }
      }
    }
  });
}
</script>
@endsection