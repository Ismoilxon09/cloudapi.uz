@extends('admin.layout')

@section('title', 'Chat — ' . $session->title)
@section('page_title', 'Chat sessiyasi')

@push('styles')
<style>
.chat-view { padding: 24px; max-width: 900px; margin: 0 auto; }
.cv-back { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted); margin-bottom: 16px; }
.cv-back:hover { color: var(--text-strong); }
.cv-head { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; padding: 18px 20px; margin-bottom: 18px; }
.cv-title { font-size: 18px; font-weight: 800; color: var(--text-strong); margin-bottom: 8px; letter-spacing: -0.01em; }
.cv-meta { display: flex; flex-wrap: wrap; gap: 16px; font-size: 12.5px; color: var(--text-muted); }
.cv-meta b { color: var(--text-strong); font-weight: 600; }
.cv-sys { margin-top: 10px; font-size: 12px; color: var(--text-muted); padding-top: 10px; border-top: 1px solid var(--border); }

.cv-msgs { display: flex; flex-direction: column; gap: 18px; }
.cv-msg { display: flex; gap: 12px; }
.cv-avatar { width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; }
.cv-msg-user .cv-avatar { background: var(--primary); color: var(--bg-elevated); }
.cv-msg-assistant .cv-avatar { background: var(--bg-subtle); border: 1px solid var(--border); color: var(--text-strong); }
.cv-body { flex: 1; min-width: 0; }
.cv-role { font-size: 12.5px; font-weight: 700; color: var(--text-strong); margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
.cv-model { font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: var(--text-subtle); background: var(--bg-subtle); padding: 1px 6px; border-radius: 4px; font-weight: 500; }
.cv-content { font-size: 14px; line-height: 1.65; color: var(--text); white-space: pre-wrap; overflow-wrap: anywhere; }
.cv-content.empty { color: var(--text-subtle); font-style: italic; }
.cv-cost { font-size: 11px; color: var(--text-subtle); margin-top: 6px; font-family: 'JetBrains Mono', monospace; }
.cv-media { display: flex; flex-wrap: wrap; gap: 8px; margin: 8px 0; }
.cv-media img { max-width: 260px; max-height: 260px; border-radius: 10px; border: 1px solid var(--border); cursor: zoom-in; }
.cv-media audio { width: 320px; max-width: 100%; }
.cv-media video { max-width: 320px; border-radius: 10px; border: 1px solid var(--border); background: #000; }
</style>
@endpush

@section('content')
<div class="chat-view">
  <a href="{{ route('admin.chat.index') }}" class="cv-back"><span class="material-icons-round" style="font-size:16px">arrow_back</span> Chatlarga qaytish</a>

  <div class="cv-head">
    <div class="cv-title">{{ $session->title }}</div>
    <div class="cv-meta">
      <span>User: <b>{{ $session->user->name ?? '—' }}</b> ({{ $session->user->email ?? '' }})</span>
      <span>Model: <b>{{ $session->model_id ?? '—' }}</b></span>
      <span>Xabarlar: <b>{{ $messages->count() }}</b></span>
      <span>Xarajat: <b>{{ number_format($session->total_cost_uzs, 0, '.', ' ') }} UZS</b></span>
      <span>Yaratilgan: <b>{{ $session->created_at->format('M d, Y H:i') }}</b></span>
    </div>
    @if($session->system_prompt)
      <div class="cv-sys"><b>System:</b> {{ \Illuminate\Support\Str::limit($session->system_prompt, 400) }}</div>
    @endif
  </div>

  <div class="cv-msgs">
    @foreach($messages as $msg)
    <div class="cv-msg cv-msg-{{ $msg->role }}">
      <div class="cv-avatar">
        @if($msg->role === 'user'){{ strtoupper(substr($session->user->name ?? 'U', 0, 1)) }}@else<span class="material-icons-round" style="font-size:18px">smart_toy</span>@endif
      </div>
      <div class="cv-body">
        <div class="cv-role">
          {{ $msg->role === 'user' ? ($session->user->name ?? 'User') : 'Assistant' }}
          @if($msg->model_id)<span class="cv-model">{{ $msg->model_id }}</span>@endif
        </div>
        @if($msg->attachments && $msg->attachments->count())
          <div class="cv-media">
            @foreach($msg->attachments as $att)
              @if(str_starts_with($att->mime_type ?? '', 'image'))<a href="{{ $att->full_url }}" target="_blank" rel="noopener"><img src="{{ $att->full_url }}" alt=""></a>
              @elseif(str_starts_with($att->mime_type ?? '', 'audio'))<audio controls src="{{ $att->full_url }}"></audio>
              @elseif(str_starts_with($att->mime_type ?? '', 'video'))<video controls src="{{ $att->full_url }}"></video>
              @endif
            @endforeach
          </div>
        @endif
        @if($msg->content !== '')
          <div class="cv-content">{{ $msg->content }}</div>
        @elseif(!$msg->attachments || !$msg->attachments->count())
          <div class="cv-content empty">(bo'sh)</div>
        @endif
        @if($msg->cost_uzs > 0 || $msg->tokens_output)
          <div class="cv-cost">↓ {{ $msg->tokens_input }} · ↑ {{ $msg->tokens_output }} tokens · {{ number_format($msg->cost_uzs, 2) }} UZS</div>
        @endif
      </div>
    </div>
    @endforeach
  </div>
</div>
@endsection
