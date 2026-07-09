@extends('layouts.dashboard')

@section('title', 'Ticketlar')

@push('styles')
<style>
.tickets-page { max-width: 900px; margin: 0 auto; padding: 24px; }
.tickets-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.tickets-title { font-size: 24px; font-weight: 800; letter-spacing: -.02em; color: var(--text-strong); margin: 0; }
.tickets-subtitle { font-size: 13px; color: var(--text-muted); margin-top: 4px; }

.ticket-list { display: flex; flex-direction: column; gap: 10px; }
.ticket-item {
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: 12px; padding: 18px 20px;
  transition: all .15s; cursor: pointer; text-decoration: none;
}
.ticket-item:hover { border-color: var(--border-strong); transform: translateY(-1px); }
.ticket-item-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 6px; }
.ticket-item-id { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-muted); font-weight: 600; }
.ticket-item-status { padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.ticket-item-subject { font-size: 14.5px; font-weight: 700; color: var(--text-strong); margin: 4px 0 6px; line-height: 1.4; }
.ticket-item-preview { font-size: 12.5px; color: var(--text-muted); line-height: 1.5; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.ticket-item-meta { display: flex; align-items: center; gap: 12px; margin-top: 10px; font-size: 11.5px; color: var(--text-subtle); }
.ticket-item-meta span { display: flex; align-items: center; gap: 4px; }
.ticket-item-meta .material-icons-round { font-size: 14px; }

.tickets-empty { text-align: center; padding: 60px 20px; }
.tickets-empty-icon { font-size: 48px; color: var(--text-subtle); margin-bottom: 12px; }
.tickets-empty-text { font-size: 15px; color: var(--text-muted); margin-bottom: 16px; }
</style>
@endpush

@section('content')
<div class="tickets-page">
  <div class="tickets-header">
    <div>
      <h1 class="tickets-title">Ticketlar</h1>
      <div class="tickets-subtitle">Barcha murojaatlaringiz va admin javoblari</div>
    </div>
    <a href="{{ route('dashboard.tickets.create') }}" class="btn btn-primary">
      <span class="material-icons-round">add</span>
      Yangi ticket
    </a>
  </div>

  @if($tickets->count() > 0)
    <div class="ticket-list">
      @foreach($tickets as $t)
      <a href="{{ route('dashboard.tickets.show', $t->id) }}" class="ticket-item">
        <div class="ticket-item-header">
          <span class="ticket-item-id">#{{ $t->id }}</span>
          <span class="ticket-item-status" style="background: {{ $t->status_color }}20; color: {{ $t->status_color }}">
            {{ $t->status_label }}
          </span>
        </div>
        <div class="ticket-item-subject">{{ $t->subject }}</div>
        <div class="ticket-item-preview">{{ $t->message }}</div>
        <div class="ticket-item-meta">
          <span><span class="material-icons-round">schedule</span> {{ $t->created_at->diffForHumans() }}</span>
          @if($t->source === 'telegram')
            <span><span class="material-icons-round">telegram</span> Telegram</span>
          @else
            <span><span class="material-icons-round">language</span> Web</span>
          @endif
          @if($t->admin_reply)
            <span style="color: #10B981"><span class="material-icons-round">check_circle</span> Javob berilgan</span>
          @endif
        </div>
      </a>
      @endforeach
    </div>

    <div style="margin-top: 24px">{{ $tickets->links() }}</div>
  @else
    <div class="tickets-empty">
      <div class="tickets-empty-icon">
        <span class="material-icons-round" style="font-size: 64px">forum</span>
      </div>
      <div class="tickets-empty-text">Sizda hali ticket yo'q</div>
      <a href="{{ route('dashboard.tickets.create') }}" class="btn btn-primary">
        <span class="material-icons-round">add</span>
        Birinchi ticket yaratish
      </a>
    </div>
  @endif
</div>
@endsection