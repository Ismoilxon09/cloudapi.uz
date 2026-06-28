@extends('admin.layout')
@section('title', 'API kalitlar')
@section('page_title', 'API kalitlar')

@push('styles')
<style>
.keys-page { padding: 24px; max-width: 1400px; margin: 0 auto; }
.keys-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.filters { display: flex; gap: 8px; margin-bottom: 16px; }
.filter-input, .filter-select { padding: 8px 12px; font-size: 13px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 8px; }
.filter-input { flex: 1; max-width: 400px; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
th { text-align: left; font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; padding: 12px 16px; background: var(--bg-subtle); border-bottom: 1px solid var(--border); }
td { padding: 12px 16px; border-bottom: 1px solid var(--border); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }
.user-cell { display: flex; align-items: center; gap: 8px; }
.user-avatar { width: 26px; height: 26px; border-radius: 50%; background: var(--primary); color: var(--bg-elevated); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; }
.key-name { font-weight: 600; color: var(--text-strong); }
.key-prefix { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-muted); }
.requests { font-family: 'JetBrains Mono', monospace; }
.status-active { background: rgba(16,185,129,.12); color: var(--success); }
.status-paused { background: rgba(245,158,11,.12); color: var(--warning); }
.status-revoked { background: rgba(239,68,68,.12); color: var(--danger); }
.status-badge { display: inline-flex; padding: 2px 8px; font-size: 10px; font-weight: 700; border-radius: 99px; text-transform: uppercase; }
.pagination-wrap { padding: 16px; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div class="keys-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">API kalitlar</h1>
      <p class="page-subtitle">Barcha foydalanuvchilar kalitlari</p>
    </div>
  </div>

  <form class="filters" method="GET">
    <input type="text" name="q" class="filter-input" placeholder="User nomi yoki email..." value="{{ request('q') }}">
    <select name="status" class="filter-select" onchange="this.form.submit()">
      <option value="">Hammasi</option>
      <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Faol</option>
      <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>To'xtatilgan</option>
      <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Bekor qilingan</option>
    </select>
  </form>

  <div class="keys-card">
    <table>
      <thead>
        <tr><th>Nom</th><th>Foydalanuvchi</th><th>Prefix</th><th>So'rovlar</th><th>Oxirgi</th><th>Status</th><th>Yaratilgan</th></tr>
      </thead>
      <tbody>
        @foreach($keys as $key)
        <tr>
          <td><span class="key-name">{{ $key->name }}</span></td>
          <td>
            <div class="user-cell">
              <div class="user-avatar">{{ strtoupper(substr($key->user->name ?? '?', 0, 1)) }}</div>
              <a href="{{ route('admin.users.show', $key->user_id) }}" style="color:var(--text-strong);text-decoration:none;font-weight:500">{{ $key->user->name ?? 'Unknown' }}</a>
            </div>
          </td>
          <td><span class="key-prefix">{{ $key->key_prefix }}</span></td>
          <td><span class="requests">{{ number_format($key->total_requests) }}</span></td>
          <td><span style="color:var(--text-muted);font-size:11px">{{ $key->last_used_at?->diffForHumans() ?? '—' }}</span></td>
          <td><span class="status-badge status-{{ $key->status }}">{{ $key->status }}</span></td>
          <td><span style="font-size:11px;color:var(--text-muted)">{{ $key->created_at->format('M d, Y') }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($keys->hasPages())
      <div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $keys])</div>
    @endif
  </div>
</div>
@endsection