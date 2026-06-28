@extends('admin.layout')
@section('title', 'Audit log')
@section('page_title', 'Audit log')

@push('styles')
<style>
.adt-page { padding: 24px; max-width: 1400px; margin: 0 auto; }
.adt-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
th { text-align: left; font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; padding: 12px 16px; background: var(--bg-subtle); border-bottom: 1px solid var(--border); }
td { padding: 12px 16px; border-bottom: 1px solid var(--border); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }
.adt-action { display: inline-flex; align-items: center; gap: 6px; padding: 3px 9px; font-size: 10px; font-weight: 700; border-radius: 99px; background: var(--bg-subtle); color: var(--text-strong); font-family: 'JetBrains Mono', monospace; text-transform: uppercase; letter-spacing: 0.04em; }
.adt-time { font-size: 11px; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; }
.adt-desc { color: var(--text); }
.adt-admin { font-weight: 600; color: var(--text-strong); }
.adt-ip { font-size: 10px; color: var(--text-subtle); font-family: 'JetBrains Mono', monospace; }
.empty { text-align: center; padding: 60px 20px; color: var(--text-muted); }
.pagination-wrap { padding: 16px; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div class="adt-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">Audit log</h1>
      <p class="page-subtitle">Admin harakatlari tarixi</p>
    </div>
  </div>

  <div class="adt-card">
    @if($logs->isEmpty())
      <div class="empty">Hozircha log yo'q</div>
    @else
      <table>
        <thead>
          <tr><th>Vaqt</th><th>Admin</th><th>Harakat</th><th>Tafsilot</th><th>IP</th></tr>
        </thead>
        <tbody>
          @foreach($logs as $log)
          <tr>
            <td><span class="adt-time">{{ $log->created_at->format('M d, H:i:s') }}</span></td>
            <td><span class="adt-admin">{{ $log->admin?->name ?? 'Unknown' }}</span></td>
            <td><span class="adt-action">{{ $log->action }}</span></td>
            <td><span class="adt-desc">{{ $log->description }}</span></td>
            <td><span class="adt-ip">{{ $log->ip ?? '—' }}</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @if($logs->hasPages())
        <div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $logs])</div>
      @endif
    @endif
  </div>
</div>
@endsection