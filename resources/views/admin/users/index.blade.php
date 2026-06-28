@extends('admin.layout')

@section('title', 'Foydalanuvchilar')
@section('page_title', 'Foydalanuvchilar')

@push('styles')
<style>
.users-page { padding: 24px; max-width: 1400px; margin: 0 auto; }

.tab-bar {
  display: flex;
  gap: 4px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 3px;
  margin-bottom: 16px;
  width: fit-content;
}

.tab-btn {
  padding: 7px 14px;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
  border-radius: 7px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.tab-btn:hover { color: var(--text-strong); }
.tab-btn.active { background: var(--text-strong); color: var(--bg-elevated); }

.tab-count {
  font-size: 10px;
  padding: 1px 6px;
  background: var(--bg-subtle);
  border-radius: 99px;
  color: var(--text-muted);
}

.tab-btn.active .tab-count { background: rgba(255,255,255,.18); color: white; }

.toolbar { display: flex; gap: 8px; margin-bottom: 16px; align-items: center; }

.search-wrap { flex: 1; max-width: 400px; position: relative; }

.search-wrap .material-icons-round {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 16px;
  color: var(--text-subtle);
}

.search-input {
  width: 100%;
  padding: 9px 14px 9px 40px;
  font-size: 13px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 9px;
  outline: none;
}

.sort-select {
  padding: 9px 14px;
  font-size: 13px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 9px;
  cursor: pointer;
}

.users-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
}

table { width: 100%; border-collapse: collapse; font-size: 13px; }

th {
  text-align: left;
  font-size: 10px; font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase; letter-spacing: 0.08em;
  padding: 12px 16px;
  background: var(--bg-subtle);
  border-bottom: 1px solid var(--border);
}

td {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border);
}

tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }

.user-cell { display: flex; align-items: center; gap: 10px; }

.user-avatar {
  width: 30px; height: 30px;
  border-radius: 50%;
  background: var(--primary);
  color: var(--bg-elevated);
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700;
}

.user-name { font-size: 13px; font-weight: 600; color: var(--text-strong); display: block; }
.user-email { font-size: 11px; color: var(--text-muted); }

.balance { font-family: 'JetBrains Mono', monospace; font-weight: 600; color: var(--text-strong); }
.status-badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 2px 8px; font-size: 10px; font-weight: 700;
  border-radius: 99px; text-transform: uppercase; letter-spacing: 0.04em;
}

.status-active { background: rgba(16,185,129,.12); color: var(--success); }
.status-blocked { background: rgba(239,68,68,.12); color: var(--danger); }

.date-meta { font-size: 12px; color: var(--text-muted); white-space: nowrap; }

.action-btn {
  width: 30px; height: 30px;
  border-radius: 6px;
  border: 1px solid var(--border);
  background: var(--bg-elevated);
  color: var(--text-muted);
  display: inline-flex; align-items: center; justify-content: center;
}

.action-btn:hover { background: var(--bg-subtle); color: var(--text-strong); border-color: var(--border-strong); }
.action-btn .material-icons-round { font-size: 14px; }

.empty { text-align: center; padding: 80px 20px; color: var(--text-muted); }
.empty .material-icons-round { font-size: 48px; color: var(--text-subtle); margin-bottom: 16px; opacity: 0.6; }
.empty h3 { font-size: 16px; color: var(--text-strong); margin-bottom: 6px; }

.pagination-wrap { padding: 16px 20px; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div class="users-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">Foydalanuvchilar</h1>
      <p class="page-subtitle">Barcha registratsiya qilingan foydalanuvchilar</p>
    </div>
  </div>

  <!-- Tabs -->
  <div class="tab-bar">
    <a href="?filter=all" class="tab-btn {{ $filter === 'all' ? 'active' : '' }}">
      Hammasi <span class="tab-count">{{ $counts['all'] }}</span>
    </a>
    <a href="?filter=active" class="tab-btn {{ $filter === 'active' ? 'active' : '' }}">
      <span class="material-icons-round" style="font-size:14px">check_circle</span>
      Faol <span class="tab-count">{{ $counts['active'] }}</span>
    </a>
    <a href="?filter=blocked" class="tab-btn {{ $filter === 'blocked' ? 'active' : '' }}">
      <span class="material-icons-round" style="font-size:14px">block</span>
      Bloklangan <span class="tab-count">{{ $counts['blocked'] }}</span>
    </a>
    <a href="?filter=new" class="tab-btn {{ $filter === 'new' ? 'active' : '' }}">
      <span class="material-icons-round" style="font-size:14px">fiber_new</span>
      Yangi (7 kun) <span class="tab-count">{{ $counts['new'] }}</span>
    </a>
  </div>

  <!-- Toolbar -->
  <form class="toolbar" method="GET">
    <input type="hidden" name="filter" value="{{ $filter }}">
    <div class="search-wrap">
      <span class="material-icons-round">search</span>
      <input type="text" name="q" class="search-input" placeholder="Ism, email, telefon..." value="{{ request('q') }}">
    </div>
    <select name="sort" class="sort-select" onchange="this.form.submit()">
      <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Yangidan eskiga</option>
      <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Eskidan yangiga</option>
      <option value="spent" {{ request('sort') === 'spent' ? 'selected' : '' }}>Sarflagan bo'yicha</option>
    </select>
  </form>

  <div class="users-card">
    @if($users->isEmpty())
      <div class="empty">
        <span class="material-icons-round">person_off</span>
        <h3>Foydalanuvchilar topilmadi</h3>
      </div>
    @else
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Balans</th>
            <th>Kalitlar</th>
            <th>Status</th>
            <th>Ro'yxatdan</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $u)
          <tr>
            <td>
              <div class="user-cell">
                <div class="user-avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                <div>
                  <span class="user-name">{{ $u->name }}</span>
                  <span class="user-email">{{ $u->email }}</span>
                </div>
              </div>
            </td>
            <td><span class="balance">{{ number_format($u->wallet?->balance_uzs ?? 0, 0, '.', ' ') }} so'm</span></td>
            <td><span style="color:var(--text-muted)">{{ $u->proxy_keys_count }} kalit</span></td>
            <td>
              @if($u->status === 'blocked')
                <span class="status-badge status-blocked"><span class="material-icons-round">block</span> Bloklangan</span>
              @else
                <span class="status-badge status-active"><span class="material-icons-round">check_circle</span> Faol</span>
              @endif
            </td>
            <td><span class="date-meta">{{ $u->created_at->format('M d, Y') }}</span></td>
            <td>
              <a href="{{ route('admin.users.show', $u) }}" class="action-btn" title="Ko'rish">
                <span class="material-icons-round">visibility</span>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      @if($users->hasPages())
        <div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $users])</div>
      @endif
    @endif
  </div>
</div>
@endsection