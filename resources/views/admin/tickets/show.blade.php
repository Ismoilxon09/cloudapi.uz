@extends('admin.layout')

@section('title', 'Ticket #' . $ticket->id)

@push('styles')
<style>
.admin-ticket-page { max-width: 1000px; margin: 0 auto; padding: 24px; }
.back-link { color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-size: 13px; margin-bottom: 16px; }
.back-link:hover { color: var(--text-strong); }

.ticket-layout { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
@media (max-width: 900px) { .ticket-layout { grid-template-columns: 1fr; } }

.ticket-main { display: flex; flex-direction: column; gap: 16px; }

.ticket-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
.ticket-card-header { padding: 20px 24px; border-bottom: 1px solid var(--border); }
.ticket-card-body { padding: 20px 24px; }

.ticket-title-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; flex-wrap: wrap; }
.ticket-id-tag { font-family: 'JetBrains Mono', monospace; font-size: 12px; padding: 4px 8px; background: var(--bg-subtle); border-radius: 6px; color: var(--text-muted); font-weight: 700; }
.status-badge, .priority-badge { padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.ticket-subject { font-size: 20px; font-weight: 800; color: var(--text-strong); letter-spacing: -.02em; margin: 8px 0 6px; line-height: 1.3; }
.ticket-meta { display: flex; gap: 14px; font-size: 12px; color: var(--text-muted); flex-wrap: wrap; }
.ticket-meta span { display: flex; align-items: center; gap: 4px; }
.ticket-meta .material-icons-round { font-size: 14px; }

.message-block { padding: 20px 24px; border-bottom: 1px solid var(--border); }
.message-block:last-child { border-bottom: 0; }
.message-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.message-avatar { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 13px; flex-shrink: 0; }
.message-avatar-user { background: var(--text-strong); color: var(--bg-elevated); }
.message-avatar-admin { background: #10B981; }
.message-name { font-weight: 700; font-size: 13.5px; color: var(--text-strong); }
.message-time { font-size: 11.5px; color: var(--text-muted); margin-top: 1px; }
.message-body { font-size: 14px; color: var(--text); line-height: 1.6; white-space: pre-wrap; padding-left: 44px; }

.reply-form { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 14px; padding: 24px; }
.reply-form h3 { font-size: 15px; font-weight: 700; color: var(--text-strong); margin: 0 0 14px; }
.reply-textarea { width: 100%; padding: 12px 14px; background: var(--bg-subtle); border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; color: var(--text-strong); font-family: inherit; min-height: 140px; resize: vertical; }
.reply-textarea:focus { outline: none; border-color: var(--text-strong); background: var(--bg-elevated); }
.reply-actions { display: flex; gap: 10px; margin-top: 14px; align-items: center; flex-wrap: wrap; }
.reply-select { padding: 10px 14px; background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 8px; font-size: 13px; }

/* Sidebar */
.ticket-sidebar { display: flex; flex-direction: column; gap: 16px; }
.sidebar-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; padding: 18px; }
.sidebar-card-title { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; }
.info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 12.5px; border-bottom: 1px solid var(--border); }
.info-row:last-child { border-bottom: 0; }
.info-row-key { color: var(--text-muted); }
.info-row-value { color: var(--text-strong); font-weight: 600; }
.quick-action-btn { display: block; width: 100%; padding: 10px 14px; margin-top: 8px; background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 8px; font-size: 13px; color: var(--text-strong); cursor: pointer; text-align: left; font-family: inherit; }
.quick-action-btn:hover { background: var(--text-strong); color: var(--bg-elevated); }
.quick-action-btn.danger:hover { background: #EF4444; color: white; border-color: #EF4444; }
</style>
@endpush

@section('content')
<div class="admin-ticket-page">
  <a href="{{ route('admin.tickets.index') }}" class="back-link">
    <span class="material-icons-round" style="font-size: 16px">arrow_back</span>
    Ticketlar
  </a>

  @if(session('success'))
    <div style="padding: 12px 16px; background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3); border-radius: 8px; color: #10B981; font-size: 13px; margin-bottom: 16px">
      ✓ {{ session('success') }}
    </div>
  @endif

  <div class="ticket-layout">
    <div class="ticket-main">
      <!-- Ticket asosiy card -->
      <div class="ticket-card">
        <div class="ticket-card-header">
          <div class="ticket-title-row">
            <span class="ticket-id-tag">#{{ $ticket->id }}</span>
            <span class="status-badge" style="background: {{ $ticket->status_color }}20; color: {{ $ticket->status_color }}">
              {{ $ticket->status_label }}
            </span>
            @php
              $priorityColor = match($ticket->priority) {
                'urgent' => '#EF4444', 'high' => '#F59E0B',
                'normal' => '#6B7280', 'low' => '#9CA3AF', default => '#6B7280',
              };
            @endphp
            <span class="priority-badge" style="background: {{ $priorityColor }}20; color: {{ $priorityColor }}">
              {{ $ticket->priority_label }}
            </span>
          </div>
          <h1 class="ticket-subject">{{ $ticket->subject }}</h1>
          <div class="ticket-meta">
            <span><span class="material-icons-round">schedule</span> {{ $ticket->created_at->format('d.m.Y H:i') }}</span>
            <span>
              @if($ticket->source === 'telegram')
                <span class="material-icons-round">telegram</span> Telegram
              @else
                <span class="material-icons-round">language</span> Web
              @endif
            </span>
            @if($ticket->replied_at)
              <span><span class="material-icons-round">reply</span> Javob: {{ $ticket->replied_at->diffForHumans() }}</span>
            @endif
          </div>
        </div>

        <!-- User xabar -->
        <div class="message-block">
          <div class="message-header">
            <div class="message-avatar message-avatar-user">
              {{ mb_substr($ticket->user?->name ?? 'U', 0, 1) }}
            </div>
            <div>
              <div class="message-name">{{ $ticket->user?->name ?? 'Foydalanuvchi' }}</div>
              <div class="message-time">{{ $ticket->created_at->diffForHumans() }}</div>
            </div>
          </div>
          <div class="message-body">{{ $ticket->message }}</div>
        </div>

        <!-- Admin javobi -->
        @if($ticket->admin_reply)
          <div class="message-block" style="background: var(--bg-subtle)">
            <div class="message-header">
              <div class="message-avatar message-avatar-admin">A</div>
              <div>
                <div class="message-name">{{ $ticket->admin?->name ?? 'Admin' }}</div>
                <div class="message-time">{{ $ticket->replied_at?->diffForHumans() }}</div>
              </div>
            </div>
            <div class="message-body">{{ $ticket->admin_reply }}</div>
          </div>
        @endif
      </div>

      <!-- Javob berish/tahrirlash formasi -->
      @if($ticket->status !== 'closed')
        <div class="reply-form">
          <h3>{{ $ticket->admin_reply ? 'Javobni tahrirlash' : 'Javob berish' }}</h3>
          <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST">
            @csrf
            <textarea name="admin_reply" class="reply-textarea" required minlength="5" placeholder="Javobingizni yozing...">{{ old('admin_reply', $ticket->admin_reply) }}</textarea>

            <div class="reply-actions">
              <select name="status" class="reply-select">
                <option value="answered" selected>Javob berildi</option>
                <option value="in_progress">Ko'rilmoqda</option>
                <option value="closed">Yopiq</option>
              </select>
              <button type="submit" class="btn btn-primary">
                <span class="material-icons-round">send</span>
                Yuborish
              </button>
            </div>
          </form>
        </div>
      @endif
    </div>

    <!-- Sidebar -->
    <aside class="ticket-sidebar">
      <div class="sidebar-card">
        <div class="sidebar-card-title">Foydalanuvchi</div>
        <div class="info-row">
          <span class="info-row-key">Ism</span>
          <span class="info-row-value">{{ $ticket->user?->name ?? '—' }}</span>
        </div>
        <div class="info-row">
          <span class="info-row-key">Email</span>
          <span class="info-row-value" style="font-family: monospace; font-size: 11.5px">{{ $ticket->user?->email ?? '—' }}</span>
        </div>
        @if($ticket->telegram_id)
        <div class="info-row">
          <span class="info-row-key">Telegram ID</span>
          <span class="info-row-value" style="font-family: monospace; font-size: 11.5px">{{ $ticket->telegram_id }}</span>
        </div>
        @endif
        @if($ticket->user)
        <div class="info-row">
          <span class="info-row-key">Ro'yxat</span>
          <span class="info-row-value">{{ $ticket->user->created_at->format('d.m.Y') }}</span>
        </div>
        @endif
      </div>

      <div class="sidebar-card">
        <div class="sidebar-card-title">Tez amallar</div>

        <!-- Status update -->
        <form action="{{ route('admin.tickets.status', $ticket->id) }}" method="POST" style="margin-bottom: 6px">
          @csrf
          @method('PATCH')
          <select name="status" onchange="this.form.submit()" class="reply-select" style="width: 100%">
            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>🟡 Ochiq</option>
            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>🔵 Ko'rilmoqda</option>
            <option value="answered" {{ $ticket->status === 'answered' ? 'selected' : '' }}>🟢 Javob berilgan</option>
            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>⚫ Yopiq</option>
          </select>
        </form>

        <!-- Priority update -->
        <form action="{{ route('admin.tickets.priority', $ticket->id) }}" method="POST" style="margin-bottom: 6px">
          @csrf
          @method('PATCH')
          <select name="priority" onchange="this.form.submit()" class="reply-select" style="width: 100%">
            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>⚪ Past</option>
            <option value="normal" {{ $ticket->priority === 'normal' ? 'selected' : '' }}>⚫ Odatiy</option>
            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>🟠 Yuqori</option>
            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>🔴 Shoshilinch</option>
          </select>
        </form>

        <!-- Delete -->
        <form action="{{ route('admin.tickets.destroy', $ticket->id) }}" method="POST" onsubmit="return confirm('Bu ticket'ni butunlay o''chirasizmi?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="quick-action-btn danger" style="margin-top: 8px">
            🗑️ Ticket'ni o'chirish
          </button>
        </form>
      </div>
    </aside>
  </div>
</div>
@endsection