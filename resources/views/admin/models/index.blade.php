@extends('admin.layout')

@section('title', 'Modellar')
@section('page_title', 'Modellar')

@push('styles')
<style>
.models-page { padding: 24px; max-width: 1600px; margin: 0 auto; }

.tab-bar { display: flex; gap: 4px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 10px; padding: 3px; margin-bottom: 16px; width: fit-content; }
.tab-btn { padding: 7px 14px; font-size: 13px; font-weight: 600; color: var(--text-muted); border-radius: 7px; display: inline-flex; align-items: center; gap: 6px; }
.tab-btn:hover { color: var(--text-strong); }
.tab-btn.active { background: var(--text-strong); color: var(--bg-elevated); }
.tab-count { font-size: 10px; padding: 1px 6px; background: var(--bg-subtle); border-radius: 99px; color: var(--text-muted); }
.tab-btn.active .tab-count { background: rgba(255,255,255,.18); color: white; }

.toolbar { display: flex; gap: 8px; margin-bottom: 16px; align-items: center; }

.search-wrap { flex: 1; max-width: 400px; position: relative; }
.search-wrap .material-icons-round { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 16px; color: var(--text-subtle); }
.search-input { width: 100%; padding: 9px 14px 9px 40px; font-size: 13px; background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 9px; outline: none; }

.models-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }

table { width: 100%; border-collapse: collapse; font-size: 12px; }
th { text-align: left; font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em; padding: 10px 14px; background: var(--bg-subtle); border-bottom: 1px solid var(--border); }
td { padding: 11px 14px; border-bottom: 1px solid var(--border); font-size: 12px; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--bg-subtle); }

.model-name { font-weight: 600; color: var(--text-strong); font-size: 13px; }
.model-id { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--text-muted); }
.price { font-family: 'JetBrains Mono', monospace; color: var(--text-strong); font-weight: 600; }
.price.free { color: var(--success); }
.price-meta { font-size: 10px; color: var(--text-muted); }

.toggle-switch { position: relative; display: inline-block; width: 32px; height: 18px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; inset: 0; background: var(--border-strong); border-radius: 99px; transition: .2s; }
.toggle-slider:before { content: ""; position: absolute; height: 14px; width: 14px; left: 2px; bottom: 2px; background: white; border-radius: 50%; transition: .2s; }
input:checked + .toggle-slider { background: var(--success); }
input:checked + .toggle-slider:before { transform: translateX(14px); }

.margin-input { width: 60px; padding: 3px 6px; font-size: 11px; border: 1px solid var(--border); border-radius: 5px; background: var(--bg-elevated); font-family: 'JetBrains Mono', monospace; text-align: center; }

.badge { display: inline-flex; align-items: center; gap: 3px; padding: 2px 7px; font-size: 9px; font-weight: 700; border-radius: 99px; text-transform: uppercase; }
.badge-free { background: rgba(16,185,129,.12); color: var(--success); }
.badge-featured { background: rgba(37,99,235,.12); color: var(--accent); }

.empty { text-align: center; padding: 60px 20px; color: var(--text-muted); }
.empty .material-icons-round { font-size: 40px; color: var(--text-subtle); margin-bottom: 12px; opacity: 0.6; }

.pagination-wrap { padding: 12px 16px; border-top: 1px solid var(--border); }
</style>
@endpush

@section('content')
<div class="models-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">Modellar</h1>
      <p class="page-subtitle">OpenRouter modellarini boshqarish</p>
    </div>
    <form action="{{ route('admin.models.sync') }}" method="POST" style="margin:0;display:inline-flex;gap:8px">
      @csrf
      <button class="btn btn-secondary">
        <span class="material-icons-round">cloud_sync</span>
        OpenRouter sync
      </button>
    </form>
    <form action="{{ route('admin.models.sync.groq') }}" method="POST" style="margin:0;display:inline-flex;gap:8px;margin-left:8px">
      @csrf
      <button class="btn btn-primary" style="background:#F55036">
        <span class="material-icons-round">bolt</span>
        Groq sync
      </button>
    </form>
  </div>

  <div class="tab-bar">
    <a href="?" class="tab-btn {{ !request('filter') ? 'active' : '' }}">
      Hammasi <span class="tab-count">{{ $counts['all'] }}</span>
    </a>
    <a href="?filter=active" class="tab-btn {{ request('filter') === 'active' ? 'active' : '' }}">
      <span class="material-icons-round" style="font-size:14px">check_circle</span>
      Faol <span class="tab-count">{{ $counts['active'] }}</span>
    </a>
    <a href="?filter=inactive" class="tab-btn {{ request('filter') === 'inactive' ? 'active' : '' }}">
      <span class="material-icons-round" style="font-size:14px">block</span>
      O'chirilgan <span class="tab-count">{{ $counts['inactive'] }}</span>
    </a>
    <a href="?filter=free" class="tab-btn {{ request('filter') === 'free' ? 'active' : '' }}">
      Bepul <span class="tab-count">{{ $counts['free'] }}</span>
    </a>
    <a href="?filter=featured" class="tab-btn {{ request('filter') === 'featured' ? 'active' : '' }}">
      <span class="material-icons-round" style="font-size:14px">star</span>
      Featured <span class="tab-count">{{ $counts['featured'] }}</span>
    </a>
  </div>

  <form class="toolbar" method="GET">
    <input type="hidden" name="filter" value="{{ request('filter') }}">
    <div class="search-wrap">
      <span class="material-icons-round">search</span>
      <input type="text" name="q" class="search-input" placeholder="Model nomi yoki ID..." value="{{ request('q') }}">
    </div>
  </form>

  <div class="models-card">
    @if($models->isEmpty())
      <div class="empty">
        <span class="material-icons-round">memory</span>
        <p>Modellar yo'q. OpenRouter sync tugmasini bosing.</p>
      </div>
    @else
      <table>
        <thead>
          <tr>
            <th>Model</th>
            <th>Kategoriya</th>
            <th>Provider</th>
            <th>Input $/M</th>
            <th>Output $/M</th>
            <th>Marja %</th>
            <th>Context</th>
            <th>Faol</th>
            <th>Featured</th>
          </tr>
        </thead>
        <tbody>
          @foreach($models as $m)
          <tr>
            <td>
              <div class="model-name">{{ $m->display_name }}</div>
              <div class="model-id">{{ $m->model_id }}</div>
              <div style="margin-top:3px;display:flex;gap:3px">
                @if($m->is_free)<span class="badge badge-free">Free</span>@endif
                @if($m->is_featured)<span class="badge badge-featured">Featured</span>@endif
              </div>
            </td>
            <td><span style="color:var(--text-muted)">{{ ucfirst($m->category ?? '—') }}</span></td>
            <td>
              @php $badge = $m->getProviderBadge(); @endphp
              <span class="badge" style="background:{{ $badge['color'] }}20;color:{{ $badge['color'] }};font-weight:600">
                {{ $badge['icon'] }} {{ $badge['label'] }}
                @if($badge['speed']) <span style="opacity:0.7">· {{ $badge['speed'] }}</span> @endif
              </span>
              <div style="font-size:10px;color:var(--text-subtle);margin-top:2px">Priority: {{ $m->priority }}</div>
            </td>
            <td>
              <span class="price {{ $m->is_free ? 'free' : '' }}">
                @if($m->is_free) Free
                @else ${{ number_format($m->cost_input_usd, 4) }}
                @endif
              </span>
            </td>
            <td>
              <span class="price {{ $m->is_free ? 'free' : '' }}">
                @if($m->is_free) Free
                @else ${{ number_format($m->cost_output_usd, 4) }}
                @endif
              </span>
            </td>
            <td>
              <form action="{{ route('admin.models.margin', $m) }}" method="POST" style="margin:0;display:inline">
                @csrf
                <input type="number" name="margin_percent" class="margin-input" value="{{ $m->margin_percent }}" step="1" min="0" max="200" onblur="this.form.submit()">
              </form>
              <span class="price-meta">%</span>
            </td>
            <td><span style="color:var(--text-muted)">{{ $m->context_length ? number_format($m->context_length / 1000) . 'K' : '—' }}</span></td>
            <td>
              <form action="{{ route('admin.models.toggle', $m) }}" method="POST" style="margin:0">
                @csrf
                <label class="toggle-switch">
                  <input type="checkbox" {{ $m->active ? 'checked' : '' }} onchange="this.form.submit()">
                  <span class="toggle-slider"></span>
                </label>
              </form>
            </td>
            <td>
              <form action="{{ route('admin.models.feature', $m) }}" method="POST" style="margin:0">
                @csrf
                <button type="submit" style="background:none;border:none;cursor:pointer">
                  <span class="material-icons-round" style="font-size:18px;color:{{ $m->is_featured ? 'var(--warning)' : 'var(--text-subtle)' }}">
                    {{ $m->is_featured ? 'star' : 'star_outline' }}
                  </span>
                </button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      @if($models->hasPages())
        <div class="pagination-wrap">@include('admin.partials.pagination', ['paginator' => $models])</div>
      @endif
    @endif
  </div>
</div>
@endsection