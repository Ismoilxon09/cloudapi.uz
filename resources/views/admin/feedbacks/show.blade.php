@extends('admin.layout')

@section('title', 'Feedback #' . $feedback->id)
@section('page_title', 'Feedback #' . $feedback->id)

@push('styles')
<style>
.fb-show-page { max-width: 800px; margin: 0 auto; padding: 24px; }

.fb-back {
  color: var(--text-muted);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  margin-bottom: 16px;
}

.fb-back:hover { color: var(--text-strong); }

.fb-header-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 16px;
}

.fb-header-top {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 16px;
}

.fb-avatar-large {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 20px;
  flex-shrink: 0;
}

.fb-user-info h2 {
  font-size: 18px;
  font-weight: 800;
  color: var(--text-strong);
  margin: 0 0 4px;
  letter-spacing: -0.02em;
}

.fb-meta-line {
  font-size: 12.5px;
  color: var(--text-muted);
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.fb-meta-line code {
  background: var(--bg-subtle);
  padding: 1px 6px;
  border-radius: 4px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 11.5px;
}

.fb-rating-large { display: flex; gap: 2px; margin-left: auto; }
.fb-rating-large .material-icons-round { font-size: 24px; }

.fb-message-text {
  font-size: 15px;
  line-height: 1.7;
  color: var(--text);
  padding: 16px 18px;
  background: var(--bg-subtle);
  border-radius: 10px;
  border: 1px solid var(--border);
  white-space: pre-wrap;
}

.fb-reply-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 16px;
}

.fb-reply-card h3 {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-strong);
  margin: 0 0 12px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.fb-reply-existing {
  padding: 14px 16px;
  background: #10B98108;
  border: 1px solid #10B98130;
  border-radius: 10px;
  margin-bottom: 14px;
}

.fb-reply-existing-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #10B981;
  margin-bottom: 6px;
}

.fb-reply-existing-text {
  font-size: 13.5px;
  color: var(--text);
  line-height: 1.5;
  white-space: pre-wrap;
}

.fb-form-field { margin-bottom: 14px; }

.fb-form-field label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.fb-form-field textarea {
  width: 100%;
  padding: 12px 14px;
  background: var(--bg-subtle);
  border: 1.5px solid var(--border);
  border-radius: 10px;
  font-size: 14px;
  color: var(--text-strong);
  font-family: inherit;
  resize: vertical;
  min-height: 100px;
}

.fb-form-field textarea:focus {
  outline: none;
  border-color: var(--text-strong);
  background: var(--bg-elevated);
}

.fb-form-actions {
  display: flex;
  gap: 8px;
}

.fb-actions-bar {
  display: flex;
  gap: 8px;
  padding: 16px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  flex-wrap: wrap;
}

.fb-btn-lg {
  padding: 10px 16px;
  font-size: 13px;
  font-weight: 600;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--bg-elevated);
  color: var(--text-strong);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.15s;
  text-decoration: none;
}

.fb-btn-lg:hover { border-color: var(--text-strong); background: var(--bg-subtle); }

.fb-btn-lg.primary {
  background: var(--text-strong);
  color: var(--bg-elevated);
  border-color: var(--text-strong);
}

.fb-btn-lg.active {
  background: #FBBF2410;
  color: #F59E0B;
  border-color: #F59E0B40;
}

.fb-btn-lg.danger { color: #EF4444; border-color: #EF444430; }
.fb-btn-lg.danger:hover { background: #EF444410; border-color: #EF4444; }
</style>
@endpush

@section('content')
<div class="fb-show-page">
  <a href="{{ route('admin.feedbacks.index') }}" class="fb-back">
    <span class="material-icons-round" style="font-size: 16px">arrow_back</span>
    Barcha feedbacks
  </a>

  @if(session('success'))
    <div style="background: #10B98110; border: 1px solid #10B98130; color: #10B981; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 13.5px; font-weight: 500;">
      {{ session('success') }}
    </div>
  @endif

  {{-- Header --}}
  <div class="fb-header-card">
    <div class="fb-header-top">
      <div class="fb-avatar-large" style="background: {{ $feedback->avatar_color }}">{{ $feedback->initial }}</div>
      <div class="fb-user-info">
        <h2>{{ $feedback->display_name }}</h2>
        <div class="fb-meta-line">
          <span>#{{ $feedback->id }}</span>
          <span>·</span>
          <span>{{ $feedback->created_at->format('d.m.Y H:i') }}</span>
          <span>·</span>
          <span>{{ $feedback->created_at->diffForHumans() }}</span>
          @if($feedback->telegram_id)
            <span>·</span>
            <span>TG: <code>{{ $feedback->telegram_id }}</code></span>
          @endif
          @if($feedback->user)
            <span>·</span>
            <span>{{ $feedback->user->email }}</span>
          @endif
        </div>
      </div>
      <div class="fb-rating-large">
        @for($i = 1; $i <= 5; $i++)
          <span class="material-icons-round" style="color: {{ $i <= $feedback->rating ? '#FBBF24' : 'var(--border)' }}">star</span>
        @endfor
      </div>
    </div>

    <div class="fb-message-text">{{ $feedback->text }}</div>
  </div>

  {{-- Reply --}}
  <div class="fb-reply-card">
    <h3>
      <span class="material-icons-round" style="font-size: 18px;">reply</span>
      {{ $feedback->admin_reply ? "Javobingiz" : "Javob berish" }}
    </h3>

    @if($feedback->admin_reply)
      <div class="fb-reply-existing">
        <div class="fb-reply-existing-label">Joriy javob · {{ $feedback->replied_at?->format('d.m.Y H:i') }}</div>
        <div class="fb-reply-existing-text">{{ $feedback->admin_reply }}</div>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.feedbacks.reply', $feedback->id) }}">
      @csrf
      <div class="fb-form-field">
        <label>{{ $feedback->admin_reply ? "Yangi javob (eskisi almashtiriladi)" : "Javob matni" }}</label>
        <textarea name="reply" required minlength="3" maxlength="1000" placeholder="Foydalanuvchi'ga javob yozing...">{{ old('reply') }}</textarea>
      </div>

      @error('reply')
        <div style="color: #EF4444; font-size: 12.5px; margin-bottom: 10px;">{{ $message }}</div>
      @enderror

      <div class="fb-form-actions">
        <button type="submit" class="fb-btn-lg primary">
          <span class="material-icons-round">send</span>
          {{ $feedback->admin_reply ? "Yangilash" : "Yuborish" }}
        </button>
        @if($feedback->telegram_id)
          <span style="font-size: 12px; color: var(--text-muted); align-self: center;">
            <span class="material-icons-round" style="font-size: 14px; vertical-align: -3px;">telegram</span>
            User Telegram orqali xabar oladi
          </span>
        @endif
      </div>
    </form>
  </div>

  {{-- Actions bar --}}
  <div class="fb-actions-bar">
    <form method="POST" action="{{ route('admin.feedbacks.toggle-publish', $feedback->id) }}" style="margin: 0;">
      @csrf
      <button type="submit" class="fb-btn-lg {{ $feedback->is_published ? 'primary' : '' }}">
        <span class="material-icons-round">{{ $feedback->is_published ? 'visibility' : 'visibility_off' }}</span>
        {{ $feedback->is_published ? 'Landing\'da ko\'rinadi' : 'Yashirin' }}
      </button>
    </form>

    <form method="POST" action="{{ route('admin.feedbacks.toggle-feature', $feedback->id) }}" style="margin: 0;">
      @csrf
      <button type="submit" class="fb-btn-lg {{ $feedback->is_featured ? 'active' : '' }}">
        <span class="material-icons-round">{{ $feedback->is_featured ? 'star' : 'star_border' }}</span>
        {{ $feedback->is_featured ? 'Featured' : 'Feature qilish' }}
      </button>
    </form>

    <form method="POST" action="{{ route('admin.feedbacks.destroy', $feedback->id) }}" style="margin-left: auto;" onsubmit="return confirm('Ushbu feedback butunlay o\'chirilsinmi? Bu amalni bekor qilish mumkin emas.');">
      @csrf
      @method('DELETE')
      <button type="submit" class="fb-btn-lg danger">
        <span class="material-icons-round">delete</span>
        O'chirish
      </button>
    </form>
  </div>
</div>
@endsection