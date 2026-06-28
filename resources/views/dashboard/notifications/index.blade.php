@extends('layouts.app')

@section('title', "Bildirishnomalar — CloudAPI")

@push('styles')
<style>
.notif-page {
  max-width: 1100px;
  margin: 0 auto;
  padding: 32px 24px;
}

.notif-page-header {
  margin-bottom: 28px;
}

.notif-page-title {
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin: 0 0 6px;
}

.notif-page-subtitle {
  font-size: 14px;
  color: var(--text-muted);
  margin: 0;
}

.notif-page-subtitle .unread-count {
  color: var(--accent);
  font-weight: 600;
}

.notif-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}

.notif-filters {
  display: flex;
  gap: 4px;
  background: var(--bg-subtle);
  padding: 4px;
  border-radius: 10px;
}

.notif-filter {
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
  background: transparent;
  border: none;
  border-radius: 7px;
  cursor: pointer;
  text-decoration: none;
  transition: all .15s;
}

.notif-filter:hover { color: var(--text-strong); }

.notif-filter.active {
  background: var(--bg-elevated);
  color: var(--text-strong);
  box-shadow: var(--shadow-sm);
}

.notif-filter .badge {
  background: var(--accent);
  color: white;
  font-size: 10px;
  padding: 1px 6px;
  border-radius: 6px;
  margin-left: 4px;
  font-weight: 700;
}

.notif-mark-all {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 7px 14px;
  font-size: 12.5px;
  font-weight: 600;
  background: transparent;
  color: var(--text-muted);
  border: 1px solid var(--border);
  border-radius: 8px;
  text-decoration: none;
  cursor: pointer;
  transition: all .15s;
}

.notif-mark-all:hover {
  background: var(--bg-subtle);
  color: var(--text-strong);
}

.notif-mark-all .material-icons-round { font-size: 16px; }

.notif-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.notif-item {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  padding: 14px 16px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  transition: all .15s;
  position: relative;
}

.notif-item:hover { border-color: var(--border-strong); }

.notif-item.unread {
  background: rgba(37, 99, 235, 0.03);
  border-color: rgba(37, 99, 235, 0.15);
}

.notif-item.unread::before {
  content: '';
  position: absolute;
  left: -4px;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 60%;
  background: var(--accent);
  border-radius: 4px;
}

.notif-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.notif-icon .material-icons-round { font-size: 22px; }

.notif-success .notif-icon { background: rgba(16, 185, 129, .12); color: #10B981; }
.notif-warning .notif-icon { background: rgba(245, 158, 11, .12); color: #F59E0B; }
.notif-info .notif-icon { background: rgba(34, 158, 217, .12); color: #229ED9; }
.notif-primary .notif-icon { background: rgba(37, 99, 235, .12); color: #2563EB; }
.notif-default .notif-icon { background: var(--bg-subtle); color: var(--text-muted); }

.notif-content {
  flex: 1;
  min-width: 0;
}

.notif-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 3px;
}

.notif-title {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--text-strong);
  letter-spacing: -.01em;
}

.notif-time {
  font-size: 11.5px;
  color: var(--text-subtle);
  margin-left: auto;
  white-space: nowrap;
}

.notif-message {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.5;
  margin: 0;
  word-wrap: break-word;
}

.notif-actions {
  display: flex;
  gap: 4px;
  align-items: center;
  flex-shrink: 0;
}

.notif-action-btn {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  color: var(--text-subtle);
  cursor: pointer;
  transition: all .15s;
  text-decoration: none;
}

.notif-action-btn:hover {
  background: var(--bg-subtle);
  color: var(--text-strong);
}

.notif-action-btn .material-icons-round { font-size: 16px; }

.notif-empty {
  text-align: center;
  padding: 80px 24px;
  color: var(--text-muted);
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
}

.notif-empty .material-icons-round {
  font-size: 56px;
  color: var(--text-subtle);
  margin-bottom: 14px;
  display: block;
}

.notif-empty h3 {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0 0 6px;
}

.notif-empty p {
  font-size: 13.5px;
  margin: 0;
}

.notif-pagination {
  margin-top: 24px;
  display: flex;
  justify-content: center;
}

@media (max-width: 640px) {
  .notif-page { padding: 20px 16px; }
  .notif-toolbar { flex-direction: column; align-items: stretch; }
  .notif-filters { width: 100%; justify-content: center; }
  .notif-mark-all { width: 100%; justify-content: center; }
  .notif-item { padding: 12px; gap: 12px; }
  .notif-icon { width: 36px; height: 36px; }
  .notif-time { display: block; margin-top: 4px; margin-left: 0; }
  .notif-header { flex-wrap: wrap; }
}
</style>
@endpush

@section('content')
<div class="notif-page">
  <div class="notif-page-header">
    <h1 class="notif-page-title">Bildirishnomalar</h1>
    <p class="notif-page-subtitle">
      {{ $stats['total'] }} ta xabar,
      <span class="unread-count">{{ $stats['unread'] }} ta yangi</span>
    </p>
  </div>

  <div class="notif-toolbar">
    <div class="notif-filters">
      <a href="{{ route('notifications.index') }}"
         class="notif-filter {{ $filter === 'all' ? 'active' : '' }}">
        Hammasi
      </a>
      <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
         class="notif-filter {{ $filter === 'unread' ? 'active' : '' }}">
        O'qilmagan
        @if($stats['unread'] > 0)
          <span class="badge">{{ $stats['unread'] }}</span>
        @endif
      </a>
    </div>

    @if($stats['unread'] > 0)
    <form action="{{ route('notifications.mark-all-read') }}" method="POST" style="display:inline">
      @csrf
      <button type="submit" class="notif-mark-all">
        <span class="material-icons-round">done_all</span>
        Hammasini o'qilgan qilish
      </button>
    </form>
    @endif
  </div>

  @if($notifications->isEmpty())
    <div class="notif-empty">
      <span class="material-icons-round">notifications_off</span>
      <h3>Bildirishnoma yo'q</h3>
      <p>
        @if($filter === 'unread')
          Barcha xabarlar o'qilgan ✓
        @else
          Yangi bildirishnomalar shu yerda ko'rinadi
        @endif
      </p>
    </div>
  @else
    <div class="notif-list">
      @foreach($notifications as $notif)
        <div class="notif-item {{ $notif->getColorClass() }} {{ is_null($notif->read_at) ? 'unread' : '' }}">
          <div class="notif-icon">
            <span class="material-icons-round">{{ $notif->getDisplayIcon() }}</span>
          </div>
          <div class="notif-content">
            <div class="notif-header">
              <span class="notif-title">{{ $notif->getDisplayTitle() }}</span>
              <span class="notif-time">{{ $notif->getTimeAgo() }}</span>
            </div>
            <p class="notif-message">{!! nl2br(e(strip_tags($notif->message))) !!}</p>
          </div>
          <div class="notif-actions">
            @if(is_null($notif->read_at))
              <form action="{{ route('notifications.read', $notif->id) }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="notif-action-btn" title="O'qilgan deb belgilash">
                  <span class="material-icons-round">done</span>
                </button>
              </form>
            @endif
            <form action="{{ route('notifications.destroy', $notif->id) }}" method="POST" style="display:inline"
                  onsubmit="return confirm('Bildirishnomani o\'chirasizmi?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="notif-action-btn" title="O'chirish">
                <span class="material-icons-round">close</span>
              </button>
            </form>
          </div>
        </div>
      @endforeach
    </div>

    <div class="notif-pagination">
      {{ $notifications->links() }}
    </div>
  @endif
</div>
@endsection