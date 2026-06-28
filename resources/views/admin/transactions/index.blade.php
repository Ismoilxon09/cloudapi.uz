@extends('admin.layout')
@section('title', 'Tranzaksiyalar')
@section('page_title', 'Tranzaksiyalar')

@push('styles')
<style>
.tx-page { padding: 24px; max-width: 1400px; margin: 0 auto; }
.tx-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.filters { display: flex; gap: 8px; margin-bottom: 16px; }
.filter-select { padding: 8px 12px; font-size: 13px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 8px; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
th { text-align: left; font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; padding: 12px 16px; background: var(--bg-subtle); border-bottom: 1px solid var(--border); }
td { padding: 12px 16px; border-bottom: 1px solid var(--border); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }
.user-cell { display: flex; align-items: center; gap: 8px; }
.user-avatar { width: 26px; height: 26px; border-radius: 50%; background: var(--primary); color: var(--bg-elevated); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; }
.amount { font-family: 'JetBrains Mono', monospace; font-weight: 700; }
.amount.positive { color: var(--success); }
.amount.negative { color: var(--text-muted); }
.type-badge { display: inline-flex; padding: 2px 8px; font-size: 10px; font-weight: 600; border-radius: 99px; background: var(--bg-subtle); color: var(--text-muted); text-transform: uppercase; }
.type-badge.deposit { background: rgba(16,185,129,.12); color: var(--success); }
.type-badge.usage { background: var(--bg-subtle); color: var(--text-muted); }
.status-pending { background: rgba(245,158,11,.12); color: var(--warning); }
.status-completed { background: rgba(16,185,129,.12); color: var(--success); }
.status-failed { background: rgba(239,68,68,.12); color: var(--danger); }
.tx-date { font-size: 11px; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; }
.pagination-wrap { padding: 16px; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div class="tx-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">Tranzaksiyalar</h1>
      <p class="page-subtitle">Barcha hamyon harakatlari</p>
    </div>
  </div>

  <form class="filters" method="GET">
    <select name="type" class="filter-select" onchange="this.form.submit()">
      <option value="">Hamma turlar</option>
      <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
      <option value="usage" {{ request('type') === 'usage' ? 'selected' : '' }}>Usage</option>
      <option value="bonus" {{ request('type') === 'bonus' ? 'selected' : '' }}>Bonus</option>
      <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Refund</option>
    </select>
    <select name="status" class="filter-select" onchange="this.form.submit()">
      <option value="">Hamma status</option>
      <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
      <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
      <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
    </select>
  </form>

  <div class="tx-card">
    <table>
      <thead>
        <tr><th>Vaqt</th><th>User</th><th>Turi</th><th>Miqdor</th><th>Balans</th><th>Status</th><th>Tavsif</th></tr>
      </thead>
      <tbody>
        @foreach($transactions as $tx)
        <tr>
          <td><span class="tx-date">{{ $tx->created_at->format('M d, H:i:s') }}</span></td>
          <td>
            <div class="user-cell">
              <div class="user-avatar">{{ strtoupper(substr($tx->user->name ?? '?', 0, 1)) }}</div>
              <span style="color:var(--text-strong);font-weight:500">{{ $tx->user->name ?? 'Unknown' }}</span>
            </div>
          </td>
          <td><span class="type-badge {{ $tx->type }}">{{ $tx->type }}</span></td>
          <td><span class="amount {{ $tx->amount_uzs > 0 ? 'positive' : 'negative' }}">{{ $tx->amount_uzs > 0 ? '+' : '' }}{{ number_format($tx->amount_uzs, 0, '.', ' ') }}</span></td>
          <td><span class="amount">{{ number_format($tx->balance_after ?? 0, 0, '.', ' ') }}</span></td>
          <td><span class="type-badge status-{{ $tx->status }}">{{ $tx->status }}</span></td>
          <td><span style="font-size:12px;color:var(--text-muted)">{{ \Illuminate\Support\Str::limit($tx->description, 50) }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($transactions->hasPages())
      <div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $transactions])</div>
    @endif
  </div>
</div>
@endsection