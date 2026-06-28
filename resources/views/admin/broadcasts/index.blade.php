@extends('admin.layout')
@section('title', 'Yuborilgan xabarlar')
@section('page_title', 'Yuborish')

@push('styles')
<style>
.bc-page { padding: 24px; max-width: 1200px; margin: 0 auto; }
.bc-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; padding: 12px 16px; background: var(--bg-subtle); border-bottom: 1px solid var(--border); }
td { padding: 14px 16px; border-bottom: 1px solid var(--border); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }
.bc-msg { font-weight: 500; color: var(--text-strong); max-width: 400px; }
.bc-channel { display: inline-flex; padding: 3px 9px; font-size: 10px; font-weight: 700; border-radius: 99px; background: var(--bg-subtle); color: var(--text-muted); text-transform: uppercase; }
.bc-channel.telegram { background: rgba(59,130,246,.12); color: #3B82F6; }
.bc-status { display: inline-flex; padding: 2px 8px; font-size: 10px; font-weight: 700; border-radius: 99px; }
.bc-status.sent { background: rgba(16,185,129,.12); color: var(--success); }
.bc-stats { font-family: 'JetBrains Mono', monospace; font-weight: 600; }
.empty { text-align: center; padding: 80px 20px; color: var(--text-muted); }
.empty .material-icons-round { font-size: 48px; color: var(--text-subtle); margin-bottom: 16px; opacity: 0.6; }
.empty h3 { font-size: 16px; color: var(--text-strong); margin-bottom: 6px; }
.pagination-wrap { padding: 16px; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div class="bc-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">Yuborilgan xabarlar</h1>
      <p class="page-subtitle">Userlarga yuborilgan broadcast tarixi</p>
    </div>
    <a href="{{ route('admin.broadcasts.create') }}" class="btn btn-primary">
      <span class="material-icons-round">campaign</span>
      Yangi xabar
    </a>
  </div>

  <div class="bc-card">
    @if($broadcasts->isEmpty())
      <div class="empty">
        <span class="material-icons-round">campaign</span>
        <h3>Hozircha xabarlar yo'q</h3>
        <p>Birinchi broadcast yuborish uchun yuqoridagi tugmani bosing</p>
      </div>
    @else
      <table>
        <thead>
          <tr><th>Sana</th><th>Kanal</th><th>Xabar</th><th>Olganlar</th><th>Status</th></tr>
        </thead>
        <tbody>
          @foreach($broadcasts as $bc)
          <tr>
            <td><span style="color:var(--text-muted);font-family:'JetBrains Mono',monospace;font-size:11px">{{ \Carbon\Carbon::parse($bc->created_at)->format('M d, H:i') }}</span></td>
            <td><span class="bc-channel {{ $bc->channel }}">{{ $bc->channel }}</span></td>
            <td><div class="bc-msg">{{ \Illuminate\Support\Str::limit($bc->message, 80) }}</div></td>
            <td><span class="bc-stats">{{ $bc->sent_count }} / {{ $bc->total_recipients }}</span></td>
            <td><span class="bc-status sent">{{ $bc->status }}</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @if($broadcasts->hasPages())
        <div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $broadcasts])</div>
      @endif
    @endif
  </div>
</div>
@endsection