@extends('admin.layout')

@section('title', 'Ticketlar boshqaruvi')

@push('styles')
<style>
.admin-tickets-page { padding: 24px; max-width: 1400px; margin: 0 auto; }
.admin-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.admin-title { font-size: 24px; font-weight: 800; letter-spacing: -.02em; color: var(--text-strong); margin: 0; }

.stats-row { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 24px; }
.stat-box { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 10px; padding: 16px; }
.stat-box-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; font-weight: 600; margin-bottom: 6px; }
.stat-box-value { font-size: 22px; font-weight: 800; color: var(--text-strong); letter-spacing: -.02em; }
.stat-box.urgent { border-color: #EF4444; }
.stat-box.urgent .stat-box-value { color: #EF4444; }

.filter-bar { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 16px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.filter-select, .filter-search { padding: 8px 12px; background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 8px; font-size: 13px; color: var(--text-strong); }
.filter-search { flex: 1; min-width: 200px; }
.filter-btn { padding: 8px 16px; background: var(--text-strong); color: var(--bg-elevated); border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
.filter-clear { padding: 8px 16px; background: transparent; color: var(--text-muted); border: 1px solid var(--border); border-radius: 8px; font-size: 13px; text-decoration: none; }

.ticket-table { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.ticket-row { display: grid; grid-template-columns: 60px 1fr 140px 100px 100px 140px 100px; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--border); align-items: center; text-decoration: none; color: var(--text); transition: background .1s; }
.ticket-row:last-child { border-bottom: 0; }
.ticket-row:hover { background: var(--bg-subtle); }
.ticket-row-header { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; font-weight: 700; color: var(--text-muted); background: var(--bg-subtle); }

.ticket-id { font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 700; color: var(--text-muted); }
.ticket-subject { min-width: 0; }
.ticket-subject-text { font-size: 13.5px; font-weight: 700; color: var(--text-strong); margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ticket-subject-meta { font-size: 11.5px; color: var(--text-muted); }
.ticket-user { font-size: 12.5px; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.status-badge, .priority-badge { padding: 3px 8px; border-radius: 999px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; display: inline-block; text-align: center; min-width: 70px; }
.ticket-time { font-size: 11.5px; color: var(--text-muted); }
.ticket-actions { display: flex; gap: 6px; }
.ticket-btn { padding: 5px 10px; background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 6px; font-size: 11px; font-weight: 600; color: var(--text-strong); text-decoration: none; }
.ticket-btn:hover { background: var(--text-strong); color: var(--bg-elevated); }

.pagination { margin-top: 20px; }

@media (max-width: 1024px) {
  .stats-row { grid-template-columns: repeat(3, 1fr); }
  .ticket-row { grid-template-columns: 1fr; gap: 6px; padding: 14px; }
  .ticket-row-header { display: none; }
  .ticket-row > div { padding: 2px 0; }
}
@media (max-width: 640px) {
  .stats-row { grid-template-columns: repeat(2, 1fr); }
  .filter-bar { flex-direction: column; align-items: stretch; }
}
</style>
@endpush

@section('content')
<div class="admin-tickets-page">
  <div class="admin-header">
    <div>
      <h1 class="admin-title">Ticketlar boshqaruvi</h1>
      <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px">Barcha foydalanuvchi murojaatlari</div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 16px; padding: 12px 16px; background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3); border-radius: 8px; color: #10B981; font-size: 13px">
      ✓ {{ session('success') }}
    </div>
  @endif

  <div class="stats-row">
    <div class="stat-box">
      <div class="stat-box-label">Jami</div>
      <div class="stat-box-value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-box">
      <div class="stat-box-label">Ochiq</div>
      <div class="stat-box-value">{{ $stats['open'] }}</div>
    </div>
    <div class="stat-box">
      <div class="stat-box-label">Ko'rilmoqda</div>
      <div class="stat-box-value">{{ $stats['in_progress'] }}</div>
    </div>
    <div class="stat-box">
      <div class="stat-box-label">Javob berilgan</div>
      <div class="stat-box-value">{{ $stats['answered'] }}</div>
    </div>
    <div class="stat-box">
      <div class="stat-box-label">Yopilgan</div>
      <div class="stat-box-value">{{ $stats['closed'] }}</div>
    </div>
    <div class="stat-box urgent">
      <div class="stat-box-label">Shoshilinch</div>
      <div class="stat-box-value">{{ $stats['urgent'] }}</div>
    </div>
  </div>

  <form method="GET" class="filter-bar">
    <select name="status" class="filter-select">
      <option value="">Barcha holatlar</option>
      <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ochiq</option>
      <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Ko'rilmoqda</option>
      <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Javob berilgan</option>
      <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Yopilgan</option>
    </select>
    <select name="priority" class="filter-select">
      <option value="">Barcha darajalar</option>
      <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Shoshilinch</option>
      <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Yuqori</option>
      <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Odatiy</option>
      <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Past</option>
    </select>
    <select name="source" class="filter-select">
      <option value="">Barcha manbalar</option>
      <option value="web" {{ request('source') === 'web' ? 'selected' : '' }}>Web</option>
      <option value="telegram" {{ request('source') === 'telegram' ? 'selected' : '' }}>Telegram</option>
    </select>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Qidirish (ID, sarlavha, foydalanuvchi)" class="filter-search">
    <button type="submit" class="filter-btn">Filter</button>
    @if(request()->hasAny(['status', 'priority', 'source', 'search']))
      <a href="{{ route('admin.tickets.index') }}" class="filter-clear">Tozalash</a>
    @endif
  </form>

  <div class="ticket-table">
    <div class="ticket-row ticket-row-header">
      <div>ID</div>
      <div>Sarlavha</div>
      <div>Foydalanuvchi</div>
      <div>Holat</div>
      <div>Muhimlik</div>
      <div>Sana</div>
      <div>Amal</div>
    </div>

    @forelse($tickets as $t)
      <a href="{{ route('admin.tickets.show', $t->id) }}" class="ticket-row">
        <div class="ticket-id">#{{ $t->id }}</div>
        <div class="ticket-subject">
          <div class="ticket-subject-text">{{ $t->subject }}</div>
          <div class="ticket-subject-meta">
            {{ $t->source === 'telegram' ? '📱 Telegram' : '🌐 Web' }}
            @if($t->admin_reply)
              · ✅ Javob berilgan
            @endif
          </div>
        </div>
        <div class="ticket-user">{{ $t->user?->name ?? '—' }}</div>
        <div>
          <span class="status-badge" style="background: {{ $t->status_color }}20; color: {{ $t->status_color }}">
            {{ $t->status_label }}
          </span>
        </div>
        <div>
          @php
            $priorityColor = match($t->priority) {
              'urgent' => '#EF4444',
              'high' => '#F59E0B',
              'normal' => '#6B7280',
              'low' => '#9CA3AF',
              default => '#6B7280',
            };
          @endphp
          <span class="priority-badge" style="background: {{ $priorityColor }}20; color: {{ $priorityColor }}">
            {{ $t->priority_label }}
          </span>
        </div>
        <div class="ticket-time">{{ $t->created_at->format('d.m.Y H:i') }}</div>
        <div class="ticket-actions">
          <span class="ticket-btn">Ko'rish →</span>
        </div>
      </a>
    @empty
      <div style="padding: 40px; text-align: center; color: var(--text-muted)">
        <span class="material-icons-round" style="font-size: 48px; color: var(--text-subtle)">forum</span>
        <div style="margin-top: 12px">Hozircha ticket yo'q</div>
      </div>
    @endforelse
  </div>

  <div class="pagination">
    {{ $tickets->links() }}
  </div>
</div>
@endsection