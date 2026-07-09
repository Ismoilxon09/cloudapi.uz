@extends('layouts.dashboard')

@section('title', 'Ticket #' . $ticket->id)

@push('styles')
<style>
.ticket-page { max-width: 800px; margin: 0 auto; padding: 24px; }
.ticket-back { color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-size: 13px; margin-bottom: 16px; }
.ticket-header { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 14px 14px 0 0; padding: 24px; border-bottom: 0; }
.ticket-title-row { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; flex-wrap: wrap; }
.ticket-id { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--text-muted); font-weight: 700; padding: 4px 8px; background: var(--bg-subtle); border-radius: 6px; }
.ticket-status { padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
.ticket-subject { font-size: 20px; font-weight: 800; color: var(--text-strong); letter-spacing: -.02em; margin: 8px 0; line-height: 1.3; }
.ticket-meta { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 12px; font-size: 12.5px; color: var(--text-muted); }
.ticket-meta span { display: flex; align-items: center; gap: 4px; }
.ticket-meta .material-icons-round { font-size: 14px; }

.ticket-message-block {
  background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 0;
  padding: 20px 24px; border-top: 0;
}
.ticket-message-user { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.ticket-message-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--text-strong); color: var(--bg-elevated); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; }
.ticket-message-name { font-weight: 700; font-size: 13.5px; color: var(--text-strong); }
.ticket-message-time { font-size: 11.5px; color: var(--text-muted); }
.ticket-message-text { font-size: 14px; color: var(--text); line-height: 1.6; white-space: pre-wrap; }

.ticket-reply-block {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0 0 14px 14px;
  padding: 20px 24px; border-top: 0;
}
.ticket-reply-user { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.ticket-reply-avatar { width: 32px; height: 32px; border-radius: 50%; background: #10B981; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; }
.ticket-reply-name { font-weight: 700; font-size: 13.5px; color: var(--text-strong); display: flex; align-items: center; gap: 6px; }
.ticket-reply-badge { padding: 2px 8px; border-radius: 999px; background: #10B98120; color: #10B981; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.ticket-reply-time { font-size: 11.5px; color: var(--text-muted); }
.ticket-reply-text { font-size: 14px; color: var(--text); line-height: 1.6; white-space: pre-wrap; }

.ticket-no-reply { padding: 40px 24px; text-align: center; background: var(--bg-subtle); border-radius: 0 0 14px 14px; border: 1px solid var(--border); border-top: 0; }
.ticket-no-reply-icon { font-size: 36px; color: var(--text-subtle); margin-bottom: 8px; }
.ticket-no-reply-text { font-size: 13px; color: var(--text-muted); }
</style>
@endpush

@section('content')
<div class="ticket-page">
  <a href="{{ route('dashboard.tickets.index') }}" class="ticket-back">
    <span class="material-icons-round" style="font-size: 16px">arrow_back</span>
    Barcha ticketlar
  </a>

  <div class="ticket-header">
    <div class="ticket-title-row">
      <span class="ticket-id">#{{ $ticket->id }}</span>
      <span class="ticket-status" style="background: {{ $ticket->status_color }}20; color: {{ $ticket->status_color }}">
        {{ $ticket->status_label }}
      </span>
    </div>
    <h1 class="ticket-subject">{{ $ticket->subject }}</h1>
    <div class="ticket-meta">
      <span><span class="material-icons-round">schedule</span> {{ $ticket->created_at->format('d M Y, H:i') }}</span>
      <span><span class="material-icons-round">priority_high</span> {{ $ticket->priority_label }}</span>
      @if($ticket->source === 'telegram')
        <span><span class="material-icons-round">telegram</span> Telegram orqali</span>
      @else
        <span><span class="material-icons-round">language</span> Web orqali</span>
      @endif
    </div>
  </div>

  <!-- User xabar -->
  <div class="ticket-message-block">
    <div class="ticket-message-user">
      <div class="ticket-message-avatar">{{ mb_substr($ticket->user?->name ?? 'U', 0, 1) }}</div>
      <div>
        <div class="ticket-message-name">{{ $ticket->user?->name ?? 'Foydalanuvchi' }}</div>
        <div class="ticket-message-time">{{ $ticket->created_at->diffForHumans() }}</div>
      </div>
    </div>
    <div class="ticket-message-text">{{ $ticket->message }}</div>
  </div>

  <!-- Admin javob -->
  @if($ticket->admin_reply)
    <div class="ticket-reply-block">
      <div class="ticket-reply-user">
        <div class="ticket-reply-avatar">A</div>
        <div>
          <div class="ticket-reply-name">
            CloudAPI jamoasi
            <span class="ticket-reply-badge">Admin</span>
          </div>
          <div class="ticket-reply-time">{{ $ticket->replied_at?->diffForHumans() }}</div>
        </div>
      </div>
      <div class="ticket-reply-text">{{ $ticket->admin_reply }}</div>
    </div>
  @else
    <div class="ticket-no-reply">
      <div class="ticket-no-reply-icon">
        <span class="material-icons-round" style="font-size: 40px">hourglass_empty</span>
      </div>
      <div class="ticket-no-reply-text">
        Ticketingiz ko'rib chiqilmoqda. Tez orada javob olasiz.
      </div>
    </div>
  @endif
</div>
@endsection