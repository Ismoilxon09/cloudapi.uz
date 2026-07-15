@extends('admin.layout')

@section('title', 'AI Agentlar')
@section('page_title', 'AI Agentlar')

@push('styles')
<style>
.aa { padding: 24px; max-width: 1400px; margin: 0 auto; }
.stat-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 20px; }
.stat-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; padding: 15px 16px; }
.stat-label { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-subtle); }
.stat-value { font-size: 22px; font-weight: 800; color: var(--text-strong); margin-top: 4px; letter-spacing: -0.02em; }
.stat-value small { font-size: 12px; font-weight: 600; color: var(--text-muted); }
@media (max-width: 1000px) { .stat-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 600px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }

.toolbar { display: flex; gap: 8px; margin-bottom: 16px; align-items: center; flex-wrap: wrap; }
.search-wrap { flex: 1; max-width: 420px; position: relative; }
.search-wrap .material-icons-round { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 16px; color: var(--text-subtle); }
.search-input { width: 100%; padding: 9px 14px 9px 40px; font-size: 13px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 9px; outline: none; }
.seg { display: inline-flex; border: 1px solid var(--border); border-radius: 9px; overflow: hidden; }
.seg a { padding: 8px 13px; font-size: 12px; font-weight: 600; color: var(--text-muted); border-left: 1px solid var(--border); }
.seg a:first-child { border-left: none; }
.seg a.on { background: var(--text-strong); color: var(--bg-elevated); }

.card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.tbl-scroll { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 760px; }
th { text-align: left; font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; padding: 12px 16px; background: var(--bg-subtle); border-bottom: 1px solid var(--border); }
td { padding: 12px 16px; border-bottom: 1px solid var(--border); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }
.user-cell { display: flex; align-items: center; gap: 10px; }
.user-avatar { width: 30px; height: 30px; border-radius: 50%; background: var(--primary); color: var(--bg-elevated); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
.user-name { font-size: 13px; font-weight: 600; color: var(--text-strong); display: block; }
.user-email { font-size: 11px; color: var(--text-muted); }
.ag-name { font-weight: 600; color: var(--text-strong); }
.chip-row { display: inline-flex; gap: 5px; }
.mini { display: inline-flex; align-items: center; gap: 3px; font-size: 11px; color: var(--text-muted); background: var(--bg-subtle); border-radius: 6px; padding: 2px 7px; }
.mini .material-icons-round { font-size: 13px; }
.num { font-family: 'JetBrains Mono', monospace; font-weight: 600; color: var(--text-strong); }
.acts { display: flex; gap: 6px; }
.action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-elevated); color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; }
.action-btn:hover { background: var(--bg-subtle); color: var(--text-strong); border-color: var(--border-strong); }
.action-btn.danger:hover { color: var(--danger); border-color: var(--danger); }
.action-btn .material-icons-round { font-size: 15px; }
.empty { text-align: center; padding: 80px 20px; color: var(--text-muted); }
.empty .material-icons-round { font-size: 48px; color: var(--text-subtle); margin-bottom: 16px; opacity: 0.6; }
.empty h3 { font-size: 16px; color: var(--text-strong); }
.pagination-wrap { padding: 16px 20px; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div class="aa">
  <div class="page-header">
    <div>
      <h1 class="page-title">AI Agentlar</h1>
      <p class="page-subtitle">Barcha foydalanuvchi agentlari — nazorat va boshqaruv</p>
    </div>
    <a href="{{ route('admin.vantage.index') }}" class="btn btn-secondary"><span class="material-icons-round">radar</span> Vantage</a>
  </div>

  <div class="stat-grid">
    <div class="stat-card"><div class="stat-label">Agentlar</div><div class="stat-value">{{ number_format($stats['agents']) }}</div></div>
    <div class="stat-card"><div class="stat-label">Faol</div><div class="stat-value">{{ number_format($stats['active']) }}</div></div>
    <div class="stat-card"><div class="stat-label">Telegram</div><div class="stat-value">{{ number_format($stats['telegram']) }}</div></div>
    <div class="stat-card"><div class="stat-label">Javoblar</div><div class="stat-value">{{ number_format($stats['replies']) }}</div></div>
    <div class="stat-card"><div class="stat-label">Jami sarf</div><div class="stat-value">{{ number_format($stats['spent'], 0, '.', ' ') }} <small>so'm</small></div></div>
    <div class="stat-card"><div class="stat-label">Bugungi javob</div><div class="stat-value">{{ number_format($stats['today']) }}</div></div>
  </div>

  <form class="toolbar" method="GET">
    <div class="search-wrap">
      <span class="material-icons-round">search</span>
      <input type="text" name="q" class="search-input" placeholder="Agent nomi, user, email..." value="{{ request('q') }}">
    </div>
    @if(request('status'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
    <div class="seg">
      <a href="{{ route('admin.agents.index', array_filter(['q'=>request('q')])) }}" class="{{ !request('status') ? 'on' : '' }}">Barchasi</a>
      <a href="{{ route('admin.agents.index', array_filter(['q'=>request('q'),'status'=>'active'])) }}" class="{{ request('status')==='active' ? 'on' : '' }}">Faol</a>
      <a href="{{ route('admin.agents.index', array_filter(['q'=>request('q'),'status'=>'paused'])) }}" class="{{ request('status')==='paused' ? 'on' : '' }}">To'xtatilgan</a>
    </div>
  </form>

  <div class="card">
    @if($agents->isEmpty())
      <div class="empty"><span class="material-icons-round">smart_toy</span><h3>Agent topilmadi</h3></div>
    @else
      <div class="tbl-scroll">
      <table>
        <thead><tr><th>Egasi</th><th>Agent</th><th>Kanallar</th><th>Suhbat</th><th>Javob</th><th>Sarf</th><th>Oxirgi faollik</th><th></th></tr></thead>
        <tbody>
          @foreach($agents as $a)
          <tr>
            <td>
              <div class="user-cell">
                <div class="user-avatar">{{ strtoupper(substr($a->user->name ?? '?', 0, 1)) }}</div>
                <div><span class="user-name">{{ $a->user->name ?? '—' }}</span><span class="user-email">{{ $a->user->email ?? '' }}</span></div>
              </div>
            </td>
            <td>
              <span class="ag-name">{{ $a->name }}</span><br>
              @if($a->status==='active')<span class="badge badge-success">Faol</span>
              @elseif($a->status==='paused')<span class="badge badge-warning">To'xtatilgan</span>
              @else<span class="badge">Qoralama</span>@endif
            </td>
            <td>
              <span class="chip-row">
                @if($a->telegramChannel && $a->telegramChannel->status==='active')<span class="mini"><span class="material-icons-round">send</span>TG</span>@endif
              </span>
            </td>
            <td><span class="num">{{ number_format($a->conversations_count) }}</span></td>
            <td><span class="num">{{ number_format($a->total_replies) }}</span></td>
            <td><span class="num">{{ number_format($a->total_spent_uzs, 0, '.', ' ') }}</span></td>
            <td><span class="user-email">{{ optional($a->last_active_at)->format('M d, H:i') ?? '—' }}</span></td>
            <td>
              <div class="acts">
                <a href="{{ route('admin.agents.show', $a->id) }}" class="action-btn" title="Ko'rish"><span class="material-icons-round">visibility</span></a>
                <form method="POST" action="{{ route('admin.agents.toggle', $a->id) }}">@csrf
                  <button class="action-btn" title="{{ $a->status==='active' ? 'To\'xtatish' : 'Faollashtirish' }}">
                    <span class="material-icons-round">{{ $a->status==='active' ? 'pause' : 'play_arrow' }}</span>
                  </button>
                </form>
                <form method="POST" action="{{ route('admin.agents.destroy', $a->id) }}" onsubmit="return confirm('Agent va suhbatlari o\'chiriladi. Davom etilsinmi?')">@csrf @method('DELETE')
                  <button class="action-btn danger" title="O'chirish"><span class="material-icons-round">delete_outline</span></button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      </div>
      @if($agents->hasPages())<div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $agents])</div>@endif
    @endif
  </div>
</div>
@endsection
