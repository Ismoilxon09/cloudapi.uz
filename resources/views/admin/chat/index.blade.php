@extends('admin.layout')

@section('title', 'AI Chat')
@section('page_title', 'AI Chat')

@push('styles')
<style>
.chat-admin { padding: 24px; max-width: 1400px; margin: 0 auto; }

.stat-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px; }
.stat-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; padding: 16px 18px; }
.stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-subtle); }
.stat-value { font-size: 24px; font-weight: 800; color: var(--text-strong); margin-top: 4px; letter-spacing: -0.02em; }
.stat-value small { font-size: 13px; font-weight: 600; color: var(--text-muted); }
@media (max-width: 900px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }

.topmodels { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
.topmodel { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 99px; font-size: 12px; }
.topmodel b { color: var(--text-strong); font-family: 'JetBrains Mono', monospace; font-size: 11px; }
.topmodel .cnt { color: var(--text-muted); }
.topmodel:hover { border-color: var(--border-strong); }

.toolbar { display: flex; gap: 8px; margin-bottom: 16px; align-items: center; }
.search-wrap { flex: 1; max-width: 420px; position: relative; }
.search-wrap .material-icons-round { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 16px; color: var(--text-subtle); }
.search-input { width: 100%; padding: 9px 14px 9px 40px; font-size: 13px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 9px; outline: none; }

.card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; padding: 12px 16px; background: var(--bg-subtle); border-bottom: 1px solid var(--border); }
td { padding: 13px 16px; border-bottom: 1px solid var(--border); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }
.user-cell { display: flex; align-items: center; gap: 10px; }
.user-avatar { width: 30px; height: 30px; border-radius: 50%; background: var(--primary); color: var(--bg-elevated); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
.user-name { font-size: 13px; font-weight: 600; color: var(--text-strong); display: block; }
.user-email { font-size: 11px; color: var(--text-muted); }
.sess-title { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-strong); }
.model-tag { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-muted); background: var(--bg-subtle); padding: 2px 7px; border-radius: 5px; }
.cost { font-family: 'JetBrains Mono', monospace; font-weight: 600; color: var(--text-strong); }
.date-meta { font-size: 12px; color: var(--text-muted); white-space: nowrap; }
.action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-elevated); color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; }
.action-btn:hover { background: var(--bg-subtle); color: var(--text-strong); border-color: var(--border-strong); }
.action-btn .material-icons-round { font-size: 14px; }
.filter-chip { display: inline-flex; align-items: center; gap: 6px; padding: 0 12px; height: 38px; border: 1px solid var(--border); border-radius: 9px; font-size: 12px; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; }
.filter-chip:hover { color: var(--text-strong); border-color: var(--border-strong); }
.empty { text-align: center; padding: 80px 20px; color: var(--text-muted); }
.empty .material-icons-round { font-size: 48px; color: var(--text-subtle); margin-bottom: 16px; opacity: 0.6; }
.empty h3 { font-size: 16px; color: var(--text-strong); }
.pagination-wrap { padding: 16px 20px; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div class="chat-admin">
  <div class="page-header">
    <div>
      <h1 class="page-title">AI Chat</h1>
      <p class="page-subtitle">Barcha foydalanuvchilar chat sessiyalari — read-only nazorat</p>
    </div>
  </div>

  <div class="stat-grid">
    <div class="stat-card"><div class="stat-label">Sessiyalar</div><div class="stat-value">{{ number_format($stats['sessions']) }}</div></div>
    <div class="stat-card"><div class="stat-label">Xabarlar</div><div class="stat-value">{{ number_format($stats['messages']) }}</div></div>
    <div class="stat-card"><div class="stat-label">Jami xarajat</div><div class="stat-value">{{ number_format($stats['cost'], 0, '.', ' ') }} <small>UZS</small></div></div>
    <div class="stat-card"><div class="stat-label">Faol userlar</div><div class="stat-value">{{ number_format($stats['users']) }}</div></div>
    <div class="stat-card"><div class="stat-label">Bugungi xabar</div><div class="stat-value">{{ number_format($stats['today']) }}</div></div>
  </div>

  @if($topModels->count())
  <div class="topmodels">
    @foreach($topModels as $tm)
      <a href="?model={{ urlencode($tm->model_id) }}" class="topmodel"><b>{{ $tm->model_id }}</b> <span class="cnt">{{ number_format($tm->cnt) }}</span></a>
    @endforeach
  </div>
  @endif

  <form class="toolbar" method="GET">
    <div class="search-wrap">
      <span class="material-icons-round">search</span>
      <input type="text" name="q" class="search-input" placeholder="User, email yoki sarlavha..." value="{{ request('q') }}">
    </div>
    @if(request('model'))
      <a href="{{ route('admin.chat.index', array_filter(['q' => request('q')])) }}" class="filter-chip" title="Model filtrini olib tashlash">
        <span class="material-icons-round" style="font-size:15px">filter_alt_off</span> {{ request('model') }}
      </a>
    @endif
  </form>

  <div class="card">
    @if($sessions->isEmpty())
      <div class="empty"><span class="material-icons-round">forum</span><h3>Sessiya topilmadi</h3></div>
    @else
      <table>
        <thead><tr><th>User</th><th>Sarlavha</th><th>Model</th><th>Xabar</th><th>Xarajat</th><th>Oxirgi faollik</th><th></th></tr></thead>
        <tbody>
          @foreach($sessions as $s)
          <tr>
            <td>
              <div class="user-cell">
                <div class="user-avatar">{{ strtoupper(substr($s->user->name ?? '?', 0, 1)) }}</div>
                <div><span class="user-name">{{ $s->user->name ?? '—' }}</span><span class="user-email">{{ $s->user->email ?? '' }}</span></div>
              </div>
            </td>
            <td><div class="sess-title">{{ $s->title }}</div></td>
            <td>@if($s->model_id)<span class="model-tag">{{ $s->model_id }}</span>@endif</td>
            <td>{{ $s->messages_count }}</td>
            <td><span class="cost">{{ number_format($s->total_cost_uzs, 0, '.', ' ') }}</span></td>
            <td><span class="date-meta">{{ optional($s->last_message_at)->format('M d, H:i') ?? $s->created_at->format('M d') }}</span></td>
            <td><a href="{{ route('admin.chat.show', $s->id) }}" class="action-btn" title="Ko'rish"><span class="material-icons-round">visibility</span></a></td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @if($sessions->hasPages())<div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $sessions])</div>@endif
    @endif
  </div>
</div>
@endsection
