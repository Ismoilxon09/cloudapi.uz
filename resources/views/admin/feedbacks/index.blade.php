@extends('admin.layout')

@section('title', 'Feedbacks')
@section('page_title', 'Feedbacks')

@push('styles')
<style>
.fb-page { padding: 24px; }

.fb-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 14px;
  margin-bottom: 24px;
}

.fb-stat-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 18px;
}

.fb-stat-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-muted);
  margin-bottom: 6px;
}

.fb-stat-value {
  font-size: 26px;
  font-weight: 800;
  color: var(--text-strong);
  letter-spacing: -0.02em;
  display: flex;
  align-items: baseline;
  gap: 4px;
}

.fb-stat-value .material-icons-round {
  font-size: 20px;
  color: #FBBF24;
}

.fb-filters {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  align-items: center;
}

.fb-filter-chip {
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 12.5px;
  font-weight: 600;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  color: var(--text-muted);
  text-decoration: none;
  transition: all 0.15s;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.fb-filter-chip:hover { border-color: var(--border-strong); color: var(--text-strong); }
.fb-filter-chip.active { background: var(--text-strong); color: var(--bg-elevated); border-color: var(--text-strong); }

.fb-search {
  margin-left: auto;
  display: flex;
  gap: 8px;
  align-items: center;
}

.fb-search input {
  padding: 8px 14px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  color: var(--text-strong);
  min-width: 240px;
}

.fb-search input:focus { outline: none; border-color: var(--text-strong); }

.fb-search button {
  padding: 8px 14px;
  background: var(--text-strong);
  color: var(--bg-elevated);
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.fb-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.fb-item {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 18px 20px;
  transition: all 0.15s;
}

.fb-item:hover { border-color: var(--border-strong); }

.fb-item-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}

.fb-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 13.5px;
  flex-shrink: 0;
}

.fb-user-info { flex: 1; min-width: 0; }
.fb-user-name { font-weight: 700; font-size: 13.5px; color: var(--text-strong); }
.fb-user-meta { font-size: 11.5px; color: var(--text-muted); margin-top: 1px; display: flex; gap: 8px; }

.fb-rating { display: flex; gap: 1px; }
.fb-rating .material-icons-round { font-size: 16px; }

.fb-text {
  font-size: 13.5px;
  line-height: 1.6;
  color: var(--text);
  margin-bottom: 10px;
}

.fb-reply {
  padding: 10px 14px;
  background: var(--bg-subtle);
  border-left: 3px solid var(--text-strong);
  border-radius: 0 8px 8px 0;
  margin-bottom: 10px;
}

.fb-reply-label {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  margin-bottom: 4px;
}

.fb-reply-text { font-size: 12.5px; color: var(--text); line-height: 1.5; font-style: italic; }

.fb-actions {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  border-top: 1px solid var(--border);
  padding-top: 10px;
}

.fb-btn {
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--bg-elevated);
  color: var(--text-strong);
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.15s;
}

.fb-btn:hover { border-color: var(--text-strong); background: var(--bg-subtle); }

.fb-btn .material-icons-round { font-size: 14px; }

.fb-btn-danger { color: #EF4444; border-color: #EF444420; }
.fb-btn-danger:hover { background: #EF444410; border-color: #EF4444; }

.fb-btn.active {
  background: var(--text-strong);
  color: var(--bg-elevated);
  border-color: var(--text-strong);
}

.fb-badge-source {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 10.5px;
  font-weight: 600;
  background: var(--bg-subtle);
  color: var(--text-muted);
}

.fb-badge-source .material-icons-round { font-size: 12px; }

.fb-empty {
  text-align: center;
  padding: 60px 20px;
  background: var(--bg-elevated);
  border: 1px dashed var(--border);
  border-radius: 12px;
  color: var(--text-muted);
}

.fb-empty .material-icons-round { font-size: 48px; margin-bottom: 8px; opacity: 0.5; }
</style>
@endpush

@section('content')
<div class="fb-page">
  {{-- Stats --}}
  <div class="fb-stats">
    <div class="fb-stat-card">
      <div class="fb-stat-label">Jami</div>
      <div class="fb-stat-value">{{ $stats['total'] }}</div>
    </div>
    <div class="fb-stat-card">
      <div class="fb-stat-label">O'rtacha baho</div>
      <div class="fb-stat-value">
        {{ $stats['avg_rating'] }}
        <span class="material-icons-round">star</span>
      </div>
    </div>
    <div class="fb-stat-card">
      <div class="fb-stat-label">Publish qilingan</div>
      <div class="fb-stat-value">{{ $stats['published'] }}</div>
    </div>
    <div class="fb-stat-card">
      <div class="fb-stat-label">Featured</div>
      <div class="fb-stat-value">{{ $stats['featured'] }}</div>
    </div>
    <div class="fb-stat-card">
      <div class="fb-stat-label">Javob berilmagan</div>
      <div class="fb-stat-value">{{ $stats['unanswered'] }}</div>
    </div>
  </div>

  {{-- Session flash --}}
  @if(session('success'))
    <div style="background: #10B98110; border: 1px solid #10B98130; color: #10B981; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 13.5px; font-weight: 500;">
      {{ session('success') }}
    </div>
  @endif

  {{-- Filters --}}
  <div class="fb-filters">
    <a href="{{ route('admin.feedbacks.index') }}" class="fb-filter-chip {{ !$rating ? 'active' : '' }}">
      Barchasi
    </a>
    @foreach([5, 4, 3, 2, 1] as $r)
      <a href="{{ route('admin.feedbacks.index', ['rating' => $r]) }}" class="fb-filter-chip {{ $rating == $r ? 'active' : '' }}">
        {{ $r }} <span class="material-icons-round" style="font-size: 14px; color: #FBBF24;">star</span>
      </a>
    @endforeach

    <form method="GET" action="{{ route('admin.feedbacks.index') }}" class="fb-search">
      <input type="text" name="q" value="{{ $search }}" placeholder="Qidirish...">
      <button type="submit">Qidirish</button>
    </form>
  </div>

  {{-- List --}}
  @if($feedbacks->count() > 0)
    <div class="fb-list">
      @foreach($feedbacks as $fb)
      <div class="fb-item">
        <div class="fb-item-header">
          <div class="fb-avatar" style="background: {{ $fb->avatar_color }}">{{ $fb->initial }}</div>
          <div class="fb-user-info">
            <div class="fb-user-name">{{ $fb->display_name }}</div>
            <div class="fb-user-meta">
              <span>#{{ $fb->id }}</span>
              <span>·</span>
              <span>{{ $fb->created_at->format('d.m.Y H:i') }}</span>
              <span class="fb-badge-source">
                @if($fb->source === 'telegram')
                  <span class="material-icons-round">telegram</span> Telegram
                @else
                  <span class="material-icons-round">language</span> Web
                @endif
              </span>
              @if($fb->telegram_id)
                <span>·</span>
                <span>TG: <code>{{ $fb->telegram_id }}</code></span>
              @endif
            </div>
          </div>
          <div class="fb-rating">
            @for($i = 1; $i <= 5; $i++)
              <span class="material-icons-round" style="color: {{ $i <= $fb->rating ? '#FBBF24' : 'var(--border)' }}">star</span>
            @endfor
          </div>
        </div>

        <div class="fb-text">{{ $fb->text }}</div>

        @if($fb->admin_reply)
        <div class="fb-reply">
          <div class="fb-reply-label">
            <span class="material-icons-round" style="font-size: 12px; vertical-align: -2px;">reply</span>
            Javob · {{ $fb->replied_at?->format('d.m.Y H:i') }}
          </div>
          <div class="fb-reply-text">{{ $fb->admin_reply }}</div>
        </div>
        @endif

        <div class="fb-actions">
          <a href="{{ route('admin.feedbacks.show', $fb->id) }}" class="fb-btn">
            <span class="material-icons-round">visibility</span>
            {{ $fb->admin_reply ? "Tahrirlash" : "Javob berish" }}
          </a>

          <form method="POST" action="{{ route('admin.feedbacks.toggle-publish', $fb->id) }}" style="margin: 0;">
            @csrf
            <button type="submit" class="fb-btn {{ $fb->is_published ? 'active' : '' }}">
              <span class="material-icons-round">{{ $fb->is_published ? 'visibility' : 'visibility_off' }}</span>
              {{ $fb->is_published ? 'Publish ON' : 'Publish OFF' }}
            </button>
          </form>

          <form method="POST" action="{{ route('admin.feedbacks.toggle-feature', $fb->id) }}" style="margin: 0;">
            @csrf
            <button type="submit" class="fb-btn {{ $fb->is_featured ? 'active' : '' }}">
              <span class="material-icons-round">{{ $fb->is_featured ? 'star' : 'star_border' }}</span>
              {{ $fb->is_featured ? 'Featured' : 'Feature qilish' }}
            </button>
          </form>

          <form method="POST" action="{{ route('admin.feedbacks.destroy', $fb->id) }}" style="margin-left: auto;" onsubmit="return confirm('Ushbu feedback o\'chirilsinmi?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="fb-btn fb-btn-danger">
              <span class="material-icons-round">delete</span>
              O'chirish
            </button>
          </form>
        </div>
      </div>
      @endforeach
    </div>

    <div style="margin-top: 20px;">{{ $feedbacks->links() }}</div>
  @else
    <div class="fb-empty">
      <span class="material-icons-round">forum</span>
      <div>Hech qanday feedback topilmadi</div>
    </div>
  @endif
</div>
@endsection