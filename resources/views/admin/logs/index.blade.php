@extends('admin.layout')
@section('title', 'API loglar')
@section('page_title', 'API loglar')

@push('styles')
<style>
.logs-page { padding: 24px; max-width: 1600px; margin: 0 auto; }
.logs-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.filters { display: flex; gap: 8px; margin-bottom: 16px; }
.filter-select { padding: 8px 12px; font-size: 13px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 8px; }
table { width: 100%; border-collapse: collapse; font-size: 11px; }
th { text-align: left; font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; padding: 10px 14px; background: var(--bg-subtle); border-bottom: 1px solid var(--border); }
td { padding: 10px 14px; border-bottom: 1px solid var(--border); font-family: 'JetBrains Mono', monospace; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }
.log-status { display: inline-flex; padding: 2px 7px; font-size: 9px; font-weight: 700; border-radius: 99px; font-family: 'Inter', sans-serif; }
.log-status.s { background: rgba(16,185,129,.12); color: var(--success); }
.log-status.e { background: rgba(239,68,68,.12); color: var(--danger); }
.user-name { font-family: 'Inter', sans-serif; color: var(--text-strong); font-weight: 600; }
.pagination-wrap { padding: 16px; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div class="logs-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">API loglar (global)</h1>
      <p class="page-subtitle">Barcha foydalanuvchilarning API so'rovlari</p>
    </div>
  </div>

  <form class="filters" method="GET">
    <select name="model" class="filter-select" onchange="this.form.submit()">
      <option value="">Hamma modellar</option>
      @foreach($models as $m)
        <option value="{{ $m }}" {{ request('model') === $m ? 'selected' : '' }}>{{ $m }}</option>
      @endforeach
    </select>
    <select name="status" class="filter-select" onchange="this.form.submit()">
      <option value="">Hamma status</option>
      <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Muvaffaqiyatli</option>
      <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Xatolik</option>
    </select>
  </form>

  <div class="logs-card">
    <table>
      <thead>
        <tr>
          <th>Vaqt</th><th>User</th><th>Model</th><th>Tokenlar</th><th>Narx</th><th>Tezlik</th><th>Status</th><th>IP</th>
        </tr>
      </thead>
      <tbody>
        @foreach($logs as $log)
        <tr>
          <td style="color:var(--text-muted)">{{ $log->created_at->format('M d, H:i:s') }}</td>
          <td><span class="user-name">{{ $log->user->name ?? '—' }}</span></td>
          <td style="color:var(--text-strong);font-weight:600">{{ $log->model }}</td>
          <td style="color:var(--text-muted)">{{ number_format($log->tokens_in) }} → {{ number_format($log->tokens_out) }}</td>
          <td style="color:var(--text-strong);font-weight:600">{{ number_format($log->cost_uzs, 2, '.', ' ') }}</td>
          <td style="color:var(--text-muted)">{{ $log->latency_ms }}ms</td>
          <td>
            @if($log->status_code >= 200 && $log->status_code < 300)
              <span class="log-status s">{{ $log->status_code }}</span>
            @else
              <span class="log-status e">{{ $log->status_code }}</span>
            @endif
          </td>
          <td style="color:var(--text-subtle);font-size:10px">{{ $log->ip ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($logs->hasPages())
      <div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $logs])</div>
    @endif
  </div>
</div>
@endsection