@extends('admin.layout')

@section('title', 'Bildirishnomalar')
@section('page_title', 'Bildirishnomalar')

@push('styles')
<style>
.notif-page { padding: 24px; max-width: 1000px; margin: 0 auto; }

.toolbar { display: flex; gap: 8px; margin-bottom: 16px; align-items: center; }

.tab-bar {
  display: flex; gap: 4px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 3px;
  width: fit-content;
}

.tab-btn {
  padding: 7px 14px;
  font-size: 13px; font-weight: 600;
  color: var(--text-muted); border-radius: 7px;
  display: inline-flex; align-items: center; gap: 6px;
}

.tab-btn:hover { color: var(--text-strong); }
.tab-btn.active { background: var(--text-strong); color: var(--bg-elevated); }
.tab-count { font-size: 10px; padding: 1px 6px; background: var(--bg-subtle); border-radius: 99px; color: var(--text-muted); }
.tab-btn.active .tab-count { background: rgba(255,255,255,.18); color: white; }

.notif-list { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }

.notif-item {
  display: flex; gap: 12px;
  padding: 14px 18px;
  border-bottom: 1px solid var(--border);
  border-left: 3px solid transparent;
  text-decoration: none;
  color: inherit;
  transition: background .15s;
}

.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--bg-subtle); }
.notif-item.unread { background: var(--bg-subtle); }
.notif-item.unread:hover { background: var(--bg-hover); }

.notif-item.priority-urgent { border-left-color: var(--danger); }
.notif-item.priority-high { border-left-color: var(--warning); }
.notif-item.priority-normal { border-left-color: var(--accent); }
.notif-item.priority-low { border-left-color: var(--text-subtle); }

.notif-icon {
  width: 36px; height: 36px;
  border-radius: 9px;
  background: var(--bg-subtle);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.notif-icon .material-icons-round { font-size: 18px; color: var(--text-muted); }
.notif-item.priority-urgent .notif-icon { background: rgba(239,68,68,.12); }
.notif-item.priority-urgent .notif-icon .material-icons-round { color: var(--danger); }
.notif-item.priority-high .notif-icon { background: rgba(245,158,11,.12); }
.notif-item.priority-high .notif-icon .material-icons-round { color: var(--warning); }

.notif-body { flex: 1; min-width: 0; }
.notif-title { font-size: 13px; font-weight: 600; color: var(--text-strong); margin-bottom: 2px; }
.notif-message { font-size: 12px; color: var(--text-muted); line-height: 1.5; }
.notif-time {
  font-size: 11px; color: var(--text-subtle);
  margin-top: 4px;
  font-family: 'JetBrains Mono', monospace;
  white-space: nowrap;
}

.notif-unread-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--accent);
  flex-shrink: 0;
  margin-top: 6px;
}

.empty { text-align: center; padding: 80px 20px; color: var(--text-muted); }
.empty .material-icons-round { font-size: 48px; color: var(--text-subtle); margin-bottom: 16px; opacity: 0.6; }
.empty h3 { font-size: 16px; color: var(--text-strong); margin-bottom: 6px; }

.pagination-wrap { padding: 16px 20px; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div class="notif-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">Bildirishnomalar</h1>
      <p class="page-subtitle">Tizim hodisalari va xabarlar</p>
    </div>
    @if($counts['unread'] > 0)
    <form action="{{ route('admin.notifications.readAll') }}" method="POST" style="margin:0">
      @csrf
      <button class="btn btn-secondary">
        <span class="material-icons-round">done_all</span>
        Hammasini o'qildi deb belgilash
      </button>
    </form>
    @endif
  </div>

  <div class="toolbar">
    <div class="tab-bar">
      <a href="?" class="tab-btn {{ !request('filter') ? 'active' : '' }}">
        Hammasi <span class="tab-count">{{ $counts['all'] }}</span>
      </a>
      <a href="?filter=unread" class="tab-btn {{ request('filter') === 'unread' ? 'active' : '' }}">
        <span class="material-icons-round" style="font-size:14px">mark_email_unread</span>
        O'qilmagan <span class="tab-count">{{ $counts['unread'] }}</span>
      </a>
      <a href="?filter=urgent" class="tab-btn {{ request('filter') === 'urgent' ? 'active' : '' }}">
        <span class="material-icons-round" style="font-size:14px">priority_high</span>
        Shoshilinch <span class="tab-count">{{ $counts['urgent'] }}</span>
      </a>
    </div>
  </div>

  <div class="notif-list">
    @if($notifications->isEmpty())
      <div class="empty">
        <span class="material-icons-round">notifications_none</span>
        <h3>Bildirishnomalar yo'q</h3>
      </div>
    @else
      @foreach($notifications as $n)
      <a href="{{ $n->target_url ?? '#' }}" class="notif-item {{ !$n->read_at ? 'unread' : '' }} priority-{{ $n->priority }}">
        <div class="notif-icon">
          <span class="material-icons-round">
            @switch($n->type)
              @case('new_topup') payments @break
              @case('low_balance') warning @break
              @case('error') error @break
              @case('system') settings @break
              @default notifications
            @endswitch
          </span>
        </div>
        <div class="notif-body">
          <div class="notif-title">{{ $n->title }}</div>
          @if($n->message)<div class="notif-message">{{ $n->message }}</div>@endif
          <div class="notif-time">{{ $n->created_at->diffForHumans() }} · {{ $n->created_at->format('M d, H:i') }}</div>
        </div>
        @if(!$n->read_at)<div class="notif-unread-dot"></div>@endif
      </a>
      @endforeach

      @if($notifications->hasPages())
        <div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $notifications])</div>
      @endif
    @endif
  </div>
</div>
@endsection