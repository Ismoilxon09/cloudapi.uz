@extends('layouts.chat')

@section('title', $currentSession->title ?? 'Chat')

@php
  // Boshlang'ich model (server tomonda hisoblanadi)
  $bootModel = null;
  $bootModelName = 'Model tanlang';
  if ($currentSession && $currentSession->model_id) {
      $sel = $models->firstWhere('model_id', $currentSession->model_id);
      $bootModel = $currentSession->model_id;
      $bootModelName = $sel->display_name ?? $currentSession->model_id;
  } else {
      $def = $models->firstWhere('is_free', true) ?? $models->first();
      if ($def) { $bootModel = $def->model_id; $bootModelName = $def->display_name; }
  }

  $modelsJson = $models->map(fn($m) => [
      'id' => $m->model_id,
      'name' => $m->display_name,
      'provider' => $m->provider,
      'category' => $m->category,
      'free' => (bool) $m->is_free,
      'featured' => (bool) $m->is_featured,
      'ctx' => $m->context_length,
      'in' => round($m->getInputPriceUzs(), 2),
      'out' => round($m->getOutputPriceUzs(), 2),
  ])->values();
@endphp

@section('content')
<div class="chat-app">
  {{-- SIDEBAR --}}
  <aside class="chat-sidebar" id="chatSidebar">
    <div class="chat-sidebar-header">
      <a href="{{ $backUrl }}" class="chat-back-btn">
        <span class="material-icons-round">arrow_back</span>
        <span>{{ $backLabel }}</span>
      </a>

      <a href="{{ route('media.index') }}" class="chat-back-btn">
        <span class="material-icons-round">perm_media</span>
        <span>Kutubxona</span>
      </a>

      <button class="chat-new-btn" data-action="new-chat" type="button">
        <span class="material-icons-round">edit_square</span>
        Yangi chat
      </button>
    </div>

    <div class="chat-sessions" id="sessionsList">
      @if($sessions->count() > 0)
        @foreach($sessions as $s)
        <a href="{{ $navBase }}/{{ $s->id }}"
           class="chat-session-item {{ ($currentSession && $currentSession->id === $s->id) ? 'active' : '' }} {{ $s->is_pinned ? 'pinned' : '' }}"
           data-session-id="{{ $s->id }}">
          <span class="material-icons-round chat-session-pin" style="{{ $s->is_pinned ? '' : 'display:none' }}">push_pin</span>
          <span class="chat-session-title">{{ $s->title }}</span>
          <div class="chat-session-actions">
            <button data-action="pin-session" data-session-id="{{ $s->id }}" title="Mahkamlash" type="button">
              <span class="material-icons-round">push_pin</span>
            </button>
            <button data-action="rename-session" data-session-id="{{ $s->id }}" title="Nomini o'zgartirish" type="button">
              <span class="material-icons-round">edit</span>
            </button>
            <button data-action="delete-session" data-session-id="{{ $s->id }}" title="O'chirish" type="button">
              <span class="material-icons-round">delete_outline</span>
            </button>
          </div>
        </a>
        @endforeach
      @else
        <div class="chat-empty">Hozircha chat yo'q</div>
      @endif
    </div>

    <div class="chat-sidebar-footer">
      <div class="chat-balance">
        Balans: <b id="balance">{{ number_format($balance, 0, '.', ' ') }}</b> UZS
      </div>
      <button onclick="toggleTheme()" title="Mavzu" type="button">
        <span class="material-icons-round" style="font-size:14px">dark_mode</span>
      </button>
    </div>
  </aside>

  {{-- Mobil backdrop --}}
  <div class="sidebar-backdrop" id="sidebarBackdrop" data-action="close-sidebar"></div>

  {{-- MAIN CHAT --}}
  <main class="chat-main">
    <div class="chat-topbar">
      <button class="chat-icon-btn" data-action="toggle-sidebar" style="display:none" id="sidebarToggle" type="button">
        <span class="material-icons-round">menu</span>
      </button>

      <a href="{{ route('home') }}" class="chat-topbar-brand">
        <div class="chat-topbar-brand-mark">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" width="24" height="20" fill="currentColor">
            <rect x="0" y="0" width="36" height="36" rx="8"/>
            <rect x="0" y="82" width="36" height="36" rx="8"/>
            <rect x="0" y="164" width="36" height="36" rx="8"/>
            <path d="M 36 18 C 90 18, 110 60, 135 90" stroke="currentColor" stroke-width="14" fill="none" stroke-linecap="round"/>
            <path d="M 36 182 C 90 182, 110 140, 135 110" stroke="currentColor" stroke-width="14" fill="none" stroke-linecap="round"/>
            <rect x="36" y="93" width="100" height="14" rx="3"/>
            <rect x="130" y="65" width="70" height="70" rx="14"/>
            <line x1="200" y1="100" x2="230" y2="100" stroke="currentColor" stroke-width="10" stroke-linecap="round"/>
            <polygon points="225,90 240,100 225,110"/>
          </svg>
        </div>
        <span>CloudAPI Chat</span>
      </a>

      @if($currentSession)
        <div class="chat-title-edit" id="chatTitleEdit" data-session-id="{{ $currentSession->id }}">
          <span class="chat-title-text" id="chatTitleText">{{ $currentSession->title }}</span>
          <button class="chat-title-btn" data-action="rename-current" title="Nomini o'zgartirish" type="button">
            <span class="material-icons-round">edit</span>
          </button>
          <button class="chat-title-btn" data-action="open-settings" title="Sozlamalar" type="button">
            <span class="material-icons-round">tune</span>
          </button>
        </div>
      @endif

      <div style="flex:1"></div>

      <div style="position: relative;">
        <button class="model-picker" data-action="toggle-models" id="modelPickerBtn" type="button">
          <span class="model-picker-logo" id="currentModelLogo"></span>
          <span id="currentModelName">{{ $bootModelName }}</span>
          <span class="material-icons-round" style="margin-left:auto">expand_more</span>
        </button>

        <div class="model-dropdown" id="modelDropdown">
          <div class="model-dropdown-search">
            <input type="text" placeholder="Model yoki provayder qidiring..." id="modelSearch">
          </div>
          <div class="model-filters" id="modelFilters">
            <button class="model-filter active" data-filter="all" type="button">Barchasi</button>
            <button class="model-filter" data-filter="image" type="button">Rasm</button>
            <button class="model-filter" data-filter="audio" type="button">Audio</button>
            <button class="model-filter" data-filter="video" type="button">Video</button>
            <button class="model-filter" data-filter="free" type="button">Bepul</button>
            <button class="model-filter" data-filter="vision" type="button">Vision</button>
            <button class="model-filter" data-filter="reasoning" type="button">Reasoning</button>
            <button class="model-filter" data-filter="code" type="button">Kod</button>
          </div>
          <div class="model-list" id="modelList"></div>
        </div>
      </div>
    </div>

    {{-- MESSAGES --}}
    <div class="chat-messages" id="chatMessages">
      <div class="chat-messages-inner" id="messagesInner">
        @if($messages->count() === 0 && !$currentSession)
          <div class="chat-welcome">
            <span class="material-icons-round chat-welcome-icon">chat_bubble_outline</span>
            <h2>CloudAPI Chat</h2>
            <p>363+ AI model bilan bir joyda ishlang. GPT, Claude, Gemini, Llama va boshqalar.</p>

            <div class="chat-suggestions">
              <button class="chat-suggestion" data-action="suggestion" type="button">Menga yangi biznes g'oyasini o'ylab top</button>
              <button class="chat-suggestion" data-action="suggestion" type="button">Python'da REST API qanday yoziladi?</button>
              <button class="chat-suggestion" data-action="suggestion" type="button">Blog post yozib ber sun'iy intellekt haqida</button>
              <button class="chat-suggestion" data-action="suggestion" type="button">Kompyuter tarmoqlarini soddalashtirib tushuntir</button>
            </div>
          </div>
        @else
          @foreach($messages as $msg)
          <div class="msg msg-{{ $msg->role }}" data-message-id="{{ $msg->id }}">
            <div class="msg-avatar" @if($msg->role !== 'user') data-model-logo="{{ $msg->model_id }}" @endif>
              @if($msg->role === 'user')
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
              @else
                <span class="material-icons-round" style="font-size: 18px;">auto_awesome</span>
              @endif
            </div>
            <div class="msg-body">
              <div class="msg-header">
                <span class="msg-name">{{ $msg->role === 'user' ? auth()->user()->name : 'Assistant' }}</span>
                @if($msg->model_id)
                  <span class="msg-model">{{ $msg->model_id }}</span>
                @endif
              </div>
              @if($msg->attachments && $msg->attachments->count())
                <div class="msg-images">
                  @foreach($msg->attachments as $att)
                    @if(str_starts_with($att->mime_type ?? '', 'image'))
                      <a href="{{ $att->full_url }}" target="_blank" rel="noopener"><img src="{{ $att->full_url }}" class="msg-image" alt=""></a>
                    @elseif(str_starts_with($att->mime_type ?? '', 'audio'))
                      <audio controls src="{{ $att->full_url }}" class="msg-audio"></audio>
                    @elseif(str_starts_with($att->mime_type ?? '', 'video'))
                      <div class="msg-video-wrap"><video controls playsinline src="{{ $att->full_url }}" class="msg-video"></video></div>
                    @endif
                  @endforeach
                </div>
              @endif
              @if($msg->role === 'user')
                @if($msg->content !== '')<div class="msg-content msg-text">{{ $msg->content }}</div>@endif
              @else
                <div class="msg-content" data-md>{{ $msg->content }}</div>
              @endif
              @if($msg->cost_uzs > 0 || $msg->tokens_output)
              <div class="msg-meta">
                @if($msg->tokens_input || $msg->tokens_output)
                  <span>↓ {{ $msg->tokens_input }} · ↑ {{ $msg->tokens_output }} tokens</span>
                @endif
                @if($msg->cost_uzs > 0)
                  <span>· {{ number_format($msg->cost_uzs, 2) }} UZS</span>
                @endif
              </div>
              @endif
            </div>
          </div>
          @endforeach
        @endif
      </div>
    </div>

    {{-- Pastga tushish tugmasi --}}
    <button class="scroll-bottom-fab" id="scrollBottomFab" data-action="scroll-bottom" title="Pastga" type="button">
      <span class="material-icons-round">arrow_downward</span>
    </button>

    {{-- INPUT --}}
    <div class="chat-input-area">
      <div class="chat-input-inner">
        <div class="chat-input-box">
          <div class="attach-previews" id="attachPreviews"></div>
          <textarea id="chatInput"
                    placeholder="Xabar yozing... (Enter yubor, Shift+Enter yangi qator)"
                    rows="1"></textarea>

          <div class="chat-input-toolbar">
            <div class="chat-input-tools">
              <button class="chat-input-tool" id="attachBtn" data-action="attach-image" title="Rasm biriktirish" type="button">
                <span class="material-icons-round">image</span>
              </button>
              <button class="chat-input-tool" title="Fayl biriktirish (tez orada)" disabled type="button">
                <span class="material-icons-round">attach_file</span>
              </button>
              <input type="file" id="attachInput" accept="image/*" multiple hidden>
            </div>

            <button class="chat-send-btn" data-action="send" id="sendBtn" type="button">
              <span>Yuborish</span>
              <span class="material-icons-round">send</span>
            </button>
            <button class="chat-stop-btn" data-action="stop" id="stopBtn" type="button">
              <span>To'xtatish</span>
              <span class="material-icons-round">stop</span>
            </button>
          </div>
        </div>

        <div class="chat-hint">
          CloudAPI Chat — 363 AI model, bitta joyda
        </div>
      </div>
    </div>
  </main>
</div>

@push('scripts')
<script>
window.CHAT_BOOT = {
  sessionId: @json($currentSession?->id),
  currentModel: @json($bootModel),
  currentModelName: @json($bootModelName),
  userName: @json(auth()->user()->name),
  sessionSystemPrompt: @json($currentSession?->system_prompt),
  sessionTemperature: @json($currentSession?->temperature),
  csrf: @json(csrf_token()),
  routes: {
    stream: @json(route('dashboard.chat.stream')),
    index: @json($indexUrl),
    base: @json($actionBase),
    viewBase: @json($navBase),
  },
};
window.CHAT_MODELS = @json($modelsJson);
</script>
@endpush
@endsection
