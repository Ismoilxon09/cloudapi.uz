@extends('layouts.app')

@section('title', 'Kutubxona')

@push('styles')
<style>
.media-page { padding: 24px; max-width: 1400px; margin: 0 auto; }
.media-head { margin-bottom: 20px; }
.media-head h1 { font-size: 24px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-strong); margin: 0 0 4px; }
.media-head p { color: var(--text-muted); font-size: 14px; margin: 0; }

.media-tabs { display: flex; gap: 6px; margin-bottom: 20px; flex-wrap: wrap; }
.media-tab {
  padding: 8px 14px; border-radius: 9px; border: 1px solid var(--border);
  background: var(--bg-elevated); color: var(--text-muted); font-size: 13px; font-weight: 600;
  text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all .15s;
}
.media-tab:hover { color: var(--text-strong); border-color: var(--border-strong); }
.media-tab.active { background: var(--primary); color: var(--bg-elevated); border-color: var(--primary); }
.media-tab .c { font-size: 11px; opacity: 0.65; }

.media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 16px; }
.media-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: border-color .15s, transform .15s; }
.media-card:hover { border-color: var(--border-strong); transform: translateY(-2px); }
.media-thumb { width: 100%; aspect-ratio: 1 / 1; background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.media-thumb img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; display: block; }
.media-thumb video { width: 100%; height: 100%; object-fit: cover; background: #000; }
.media-thumb .ico { font-size: 44px; color: var(--text-subtle); }
.media-info { padding: 10px 12px; }
.media-info audio { width: 100%; height: 34px; margin-bottom: 8px; }
.media-model { font-size: 11px; font-family: 'JetBrains Mono', monospace; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.media-metabar { display: flex; align-items: center; justify-content: space-between; margin-top: 6px; }
.media-date { font-size: 11px; color: var(--text-subtle); }
.media-dl { color: var(--text-muted); display: inline-flex; }
.media-dl:hover { color: var(--text-strong); }
.media-dl .material-icons-round { font-size: 16px; }
.media-type { position: absolute; }

.media-empty { text-align: center; padding: 90px 20px; color: var(--text-muted); }
.media-empty .material-icons-round { font-size: 52px; color: var(--text-subtle); opacity: 0.6; margin-bottom: 14px; }
.media-empty h3 { font-size: 17px; color: var(--text-strong); margin: 0 0 6px; }
.media-empty p { font-size: 14px; margin: 0 0 20px; }
.media-empty a {
  display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 10px;
  background: var(--primary); color: var(--bg-elevated); text-decoration: none; font-weight: 600; font-size: 14px;
}
.media-pagination { margin-top: 28px; }
</style>
@endpush

@section('content')
<div class="media-page">
  <div class="media-head">
    <h1>Kutubxona</h1>
    <p>Chatda yaratgan rasm, video va audiolaringiz</p>
  </div>

  <div class="media-tabs">
    <a href="{{ route('media.index') }}" class="media-tab {{ !$type ? 'active' : '' }}">Hammasi <span class="c">{{ $counts['all'] }}</span></a>
    <a href="{{ route('media.index', ['type' => 'image']) }}" class="media-tab {{ $type === 'image' ? 'active' : '' }}">Rasm <span class="c">{{ $counts['image'] }}</span></a>
    <a href="{{ route('media.index', ['type' => 'video']) }}" class="media-tab {{ $type === 'video' ? 'active' : '' }}">Video <span class="c">{{ $counts['video'] }}</span></a>
    <a href="{{ route('media.index', ['type' => 'audio']) }}" class="media-tab {{ $type === 'audio' ? 'active' : '' }}">Audio <span class="c">{{ $counts['audio'] }}</span></a>
  </div>

  @if($media->isEmpty())
    <div class="media-empty">
      <span class="material-icons-round">perm_media</span>
      <h3>Hozircha media yo'q</h3>
      <p>Chatda rasm yoki video yarating — bu yerda paydo bo'ladi.</p>
      <a href="{{ route('dashboard.chat.index') }}"><span class="material-icons-round" style="font-size:18px">forum</span> Chatga o'tish</a>
    </div>
  @else
    <div class="media-grid">
      @foreach($media as $m)
        <div class="media-card">
          <div class="media-thumb">
            @if(str_starts_with($m->mime_type ?? '', 'image'))
              <a href="{{ $m->full_url }}" target="_blank" rel="noopener"><img src="{{ $m->full_url }}" alt="" loading="lazy"></a>
            @elseif(str_starts_with($m->mime_type ?? '', 'video'))
              <video src="{{ $m->full_url }}" controls preload="metadata" playsinline></video>
            @elseif(str_starts_with($m->mime_type ?? '', 'audio'))
              <span class="material-icons-round ico">graphic_eq</span>
            @else
              <span class="material-icons-round ico">insert_drive_file</span>
            @endif
          </div>
          <div class="media-info">
            @if(str_starts_with($m->mime_type ?? '', 'audio'))
              <audio src="{{ $m->full_url }}" controls></audio>
            @endif
            <div class="media-model">{{ $m->message->model_id ?? '—' }}</div>
            <div class="media-metabar">
              <span class="media-date">{{ optional($m->created_at)->format('d M, H:i') }}</span>
              <a href="{{ $m->full_url }}" download class="media-dl" title="Yuklab olish"><span class="material-icons-round">download</span></a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="media-pagination">{{ $media->links() }}</div>
  @endif
</div>
@endsection
