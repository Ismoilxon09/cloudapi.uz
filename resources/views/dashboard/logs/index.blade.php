@extends('layouts.app')

@section('title', __('logs.title') . ' — CloudAPI')

@push('styles')
<style>
.logs-page { max-width: 1400px; margin: 0 auto; padding: 24px; }

.logs-header { margin-bottom: 20px; }
.logs-title { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-strong); margin-bottom: 4px; }
.logs-subtitle { font-size: 13px; color: var(--text-muted); }

.logs-filters { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }

.filter-select {
  padding: 8px 12px; font-size: 13px;
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: 8px; cursor: pointer; outline: none;
}

.logs-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }

.logs-table { width: 100%; border-collapse: collapse; font-size: 12px; }

.logs-table th {
  text-align: left; font-size: 10px; font-weight: 700;
  color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em;
  padding: 10px 14px; background: var(--bg-subtle); border-bottom: 1px solid var(--border);
}

.logs-table td {
  padding: 11px 14px; border-bottom: 1px solid var(--border);
  font-family: 'JetBrains Mono', monospace; font-size: 11px;
}

.logs-table tr:last-child td { border-bottom: none; }
.logs-table tr:hover td { background: var(--bg-subtle); }

.log-time { color: var(--text-muted); white-space: nowrap; }
.log-model { color: var(--text-strong); font-weight: 600; }
.log-tokens { color: var(--text-muted); }
.log-cost { color: var(--text-strong); font-weight: 600; }
.log-latency { color: var(--text-muted); }

.log-status {
  display: inline-flex; align-items: center; gap: 3px;
  padding: 2px 7px; font-size: 10px; font-weight: 700;
  border-radius: 99px; font-family: 'Inter', sans-serif;
}

.log-status.s { background: rgba(16,185,129,.12); color: var(--success); }
.log-status.e { background: rgba(239,68,68,.12); color: var(--danger); }

.empty { text-align: center; padding: 80px 20px; color: var(--text-muted); }
.empty .material-icons-round { font-size: 48px; color: var(--text-subtle); margin-bottom: 16px; opacity: 0.6; }
.empty h3 { font-size: 16px; color: var(--text-strong); margin-bottom: 6px; }
.empty p { font-size: 13px; }

.pagination-wrap { padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: center; }
</style>
@endpush

@section('content')
<div class="logs-page">
  <div class="logs-header">
    <h1 class="logs-title">{{ __('logs.title') }}</h1>
    <p class="logs-subtitle">{{ __('logs.subtitle') }}</p>
  </div>

  <form method="GET" class="logs-filters">
    <select name="model" class="filter-select" onchange="this.form.submit()">
      <option value="">{{ __('logs.filter_model') }}</option>
      @foreach($models as $m)
        <option value="{{ $m }}" {{ request('model') == $m ? 'selected' : '' }}>{{ $m }}</option>
      @endforeach
    </select>
    <select name="status" class="filter-select" onchange="this.form.submit()">
      <option value="">{{ __('logs.filter_status') }}</option>
      <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>{{ __('logs.status_success') }}</option>
      <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>{{ __('logs.status_error') }}</option>
    </select>
  </form>

  <div class="logs-card">
    @if($logs->isEmpty())
      <div class="empty">
        <span class="material-icons-round">history</span>
        <h3>{{ __('logs.empty') }}</h3>
        <p>{{ __('logs.empty_desc') }}</p>
      </div>
    @else
      <table class="logs-table">
        <thead>
          <tr>
            <th>{{ __('logs.cols.time') }}</th>
            <th>{{ __('logs.cols.model') }}</th>
            <th>{{ __('logs.cols.tokens') }}</th>
            <th>{{ __('logs.cols.cost') }}</th>
            <th>{{ __('logs.cols.latency') }}</th>
            <th>{{ __('logs.cols.status') }}</th>
            <th>{{ __('logs.cols.ip') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($logs as $log)
          <tr>
            <td class="log-time">{{ $log->created_at->format('M d, H:i:s') }}</td>
            <td class="log-model">{{ $log->model }}</td>
            <td class="log-tokens">{{ number_format($log->tokens_in) }} → {{ number_format($log->tokens_out) }}</td>
            <td class="log-cost">{{ number_format($log->cost_uzs, 2, '.', ' ') }}</td>
            <td class="log-latency">{{ $log->latency_ms }}ms</td>
            <td>
              @if($log->status_code >= 200 && $log->status_code < 300)
                <span class="log-status s">{{ $log->status_code }}</span>
              @else
                <span class="log-status e">{{ $log->status_code }}</span>
              @endif
            </td>
            <td class="log-time">{{ $log->ip ?? '—' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>

      @if($logs->hasPages())
        <div class="pagination-wrap">{{ $logs->links('vendor.pagination.cloudapi') }}</div>
      @endif
    @endif
  </div>
</div>
@endsection