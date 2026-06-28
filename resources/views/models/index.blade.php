@extends('layouts.app')

@section('title', __('models.title') . ' — CloudAPI')

@push('styles')
<style>
.models-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 24px;
  position: relative;
  z-index: 2;
}

/* Header */
.models-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.models-title-block { flex: 1; min-width: 280px; }

.models-title {
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.models-subtitle {
  font-size: 13px;
  color: var(--text-muted);
}

.models-stats {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 10px;
}

.stat-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 99px;
  font-size: 12px;
  color: var(--text-muted);
}

.stat-pill strong { color: var(--text-strong); font-weight: 700; }
.stat-pill .material-icons-round { font-size: 14px; }

/* Toolbar */
.models-toolbar {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 18px;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 260px;
  position: relative;
}

.search-box .material-icons-round {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-subtle);
  font-size: 18px;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 40px;
  font-size: 14px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  outline: none;
  transition: all .15s;
}

.search-input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}

.sort-select {
  padding: 10px 14px;
  font-size: 13px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  cursor: pointer;
  font-weight: 500;
  outline: none;
}

.toggle-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: var(--bg-elevated);
  color: var(--text-muted);
  cursor: pointer;
  transition: all .15s;
}

.toggle-pill:hover { border-color: var(--border-strong); color: var(--text-strong); }

.toggle-pill.active {
  background: var(--gray-deep);
  border-color: var(--gray-deep);
  color: white;
}

.toggle-pill input { display: none; }

.toggle-pill .material-icons-round { font-size: 14px; }

/* Category tabs */
.cat-tabs {
  display: flex;
  gap: 6px;
  margin-bottom: 16px;
  flex-wrap: wrap;
  overflow-x: auto;
  padding-bottom: 4px;
}

.cat-tab {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  cursor: pointer;
  transition: all .15s;
  text-decoration: none;
  white-space: nowrap;
}

.cat-tab:hover {
  border-color: var(--border-strong);
  color: var(--text-strong);
}

.cat-tab.active {
  background: var(--text-strong);
  border-color: var(--text-strong);
  color: var(--bg-elevated);
}

.cat-tab .cat-count {
  font-size: 11px;
  padding: 1px 6px;
  background: var(--bg-subtle);
  border-radius: 99px;
  color: var(--text-muted);
}

.cat-tab.active .cat-count {
  background: rgba(255,255,255,.18);
  color: white;
}

.cat-tab .material-icons-round { font-size: 14px; }

/* Provider tabs */
.provider-tabs {
  display: flex;
  gap: 6px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  overflow-x: auto;
  padding-bottom: 4px;
}

.provider-tab {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 11px;
  font-size: 12px;
  font-weight: 500;
  color: var(--text-muted);
  background: transparent;
  border: 1px solid var(--border);
  border-radius: 99px;
  cursor: pointer;
  transition: all .15s;
  text-decoration: none;
  white-space: nowrap;
}

.provider-tab:hover {
  border-color: var(--border-strong);
  color: var(--text-strong);
}

.provider-tab.active {
  background: var(--gray-deep);
  border-color: var(--gray-deep);
  color: white;
  font-weight: 600;
}

.provider-tab .pt-count {
  font-size: 10px;
  opacity: 0.7;
}

/* Models grid — compact */
.models-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 10px;
}

.model-tile {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 14px;
  transition: all .15s;
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
}

.model-tile:hover {
  border-color: var(--text-muted);
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.model-tile-head {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 10px;
}

.model-logo {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  padding: 5px;
}

.model-logo svg {
  width: 100%;
  height: 100%;
  display: block;
}

.model-info { flex: 1; min-width: 0; }

.model-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-strong);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 2px;
}

.model-id {
  font-size: 10px;
  color: var(--text-muted);
  font-family: 'JetBrains Mono', monospace;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.model-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 8px;
  min-height: 18px;
}

.model-badge {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 1px 6px;
  font-size: 9px;
  font-weight: 700;
  border-radius: 99px;
  background: var(--bg-subtle);
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.model-badge .material-icons-round { font-size: 10px; }

.model-badge.free {
  background: rgba(16, 185, 129, .1);
  color: var(--success);
}

.model-badge.featured {
  background: rgba(37, 99, 235, .08);
  color: var(--accent);
}

.model-badge.cat-reasoning { background: rgba(245, 158, 11, .1); color: var(--warning); }
.model-badge.cat-vision { background: rgba(139, 92, 246, .1); color: #8B5CF6; }
.model-badge.cat-code { background: rgba(99, 102, 241, .1); color: #6366F1; }

.model-desc {
  font-size: 11px;
  color: var(--text-muted);
  line-height: 1.45;
  margin-bottom: 10px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  flex: 1;
}

.model-prices {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  padding-top: 10px;
  border-top: 1px dashed var(--border);
}

.model-price { flex: 1; }

.model-price-label {
  font-size: 9px;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  font-weight: 700;
  margin-bottom: 2px;
}

.model-price-value {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-strong);
  font-family: 'JetBrains Mono', monospace;
}

.model-price-value.free { color: var(--success); }

.model-price-value .meta-suffix {
  font-size: 9px;
  color: var(--text-subtle);
  font-weight: 500;
  margin-left: 1px;
}

.model-price-usd {
  font-size: 10px;
  color: var(--text-subtle);
  font-family: 'JetBrains Mono', monospace;
  margin-top: 2px;
}

.model-context {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 10px;
  color: var(--text-muted);
  margin-top: 8px;
}

.model-context .material-icons-round { font-size: 12px; }

/* Empty state */
.empty-state {
  text-align: center;
  padding: 80px 24px;
  color: var(--text-muted);
}

.empty-state .material-icons-round {
  font-size: 48px;
  color: var(--text-subtle);
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-strong);
  margin-bottom: 6px;
}

/* Pagination */
.pagination-wrap {
  margin-top: 28px;
  display: flex;
  justify-content: center;
}

.pagination {
  display: flex;
  align-items: center;
  gap: 4px;
}

.pagination a, .pagination span {
  min-width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 10px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
  border-radius: 8px;
  text-decoration: none;
  transition: all .15s;
}

.pagination a:hover { background: var(--bg-subtle); color: var(--text); }
.pagination .active {
  background: var(--text-strong);
  color: var(--bg-elevated);
  font-weight: 600;
}

.pagination .disabled { color: var(--text-subtle); opacity: 0.5; }
</style>
@endpush

@section('content')

@guest
<header class="landing-nav">
  <div class="landing-nav-inner">
    <a href="{{ route('home') }}" class="brand">
      <div class="brand-mark">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" width="30" height="25" fill="currentColor">
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
      <span>CloudAPI</span>
    </a>
    <nav class="nav-links" style="margin-left:48px;flex:1">
      <a href="{{ route('models.index') }}" class="nav-link active">{{ __('landing.nav.models') }}</a>
      <a href="{{ route('pricing') }}" class="nav-link">{{ __('landing.nav.pricing') }}</a>
      <a href="{{ route('docs') }}" class="nav-link">{{ __('landing.nav.docs') }}</a>
    </nav>
    <div class="topbar-actions">
      @include('partials.lang-switcher')
      <button class="icon-btn" onclick="toggleTheme()"><span class="material-icons-round" id="themeIcon">dark_mode</span></button>
      <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">{{ __('landing.nav.sign_in') }}</a>
      <a href="{{ route('register') }}" class="btn btn-primary btn-sm">{{ __('landing.nav.get_started') }}</a>
    </div>
  </div>
</header>
<div style="height:64px"></div>
@endguest

<div class="models-page">
  <!-- Header -->
  <div class="models-header">
    <div class="models-title-block">
      <h1 class="models-title">{{ __('models.title') }}</h1>
      <p class="models-subtitle">{{ __('models.subtitle') }}</p>
      <div class="models-stats">
        <div class="stat-pill">
          <span class="material-icons-round">memory</span>
          <strong>{{ $totalCount }}</strong> {{ __('models.stats.models') }}
        </div>
        <div class="stat-pill">
          <span class="material-icons-round" style="color:var(--success)">free_breakfast</span>
          <strong>{{ $freeCount }}</strong> {{ __('models.stats.free') }}
        </div>
        <div class="stat-pill">
          <span class="material-icons-round">business</span>
          <strong>{{ $providers->count() }}</strong> {{ __('models.stats.providers') }}
        </div>
      </div>
    </div>
  </div>

  <form method="GET" id="filterForm">
    <!-- Toolbar -->
    <div class="models-toolbar">
      <div class="search-box">
        <span class="material-icons-round">search</span>
        <input type="text" name="q" class="search-input"
               placeholder="{{ __('models.search_placeholder') }}"
               value="{{ request('q') }}" autocomplete="off">
      </div>

      <label class="toggle-pill {{ request('free') ? 'active' : '' }}">
        <input type="checkbox" name="free" value="1" {{ request('free') ? 'checked' : '' }} onchange="this.form.submit()">
        <span class="material-icons-round">free_breakfast</span>
        {{ __('models.filters.free_only') }}
      </label>

      <label class="toggle-pill {{ request('featured') ? 'active' : '' }}">
        <input type="checkbox" name="featured" value="1" {{ request('featured') ? 'checked' : '' }} onchange="this.form.submit()">
        <span class="material-icons-round">star</span>
        {{ __('models.filters.featured') }}
      </label>

      <select name="sort" class="sort-select" onchange="this.form.submit()">
        <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>{{ __('models.sort.featured') }}</option>
        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('models.sort.price_asc') }}</option>
        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('models.sort.price_desc') }}</option>
        <option value="context" {{ request('sort') == 'context' ? 'selected' : '' }}>{{ __('models.sort.context') }}</option>
        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>{{ __('models.sort.name') }}</option>
      </select>
    </div>

    <!-- Category tabs -->
    <div class="cat-tabs">
      @php
        $catIcons = [
          'chat' => 'chat',
          'reasoning' => 'psychology',
          'vision' => 'visibility',
          'code' => 'code',
          'embedding' => 'hub',
        ];
      @endphp
      <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}"
         class="cat-tab {{ !request('category') ? 'active' : '' }}">
        <span class="material-icons-round">apps</span>
        {{ __('models.filters.all') }}
        <span class="cat-count">{{ $totalCount }}</span>
      </a>
      @foreach($categories as $cat)
      <a href="{{ request()->fullUrlWithQuery(['category' => $cat->category, 'page' => null]) }}"
         class="cat-tab {{ request('category') === $cat->category ? 'active' : '' }}">
        <span class="material-icons-round">{{ $catIcons[$cat->category] ?? 'circle' }}</span>
        {{ __("models.category.{$cat->category}", [], 'en') ?? ucfirst($cat->category) }}
        <span class="cat-count">{{ $cat->count }}</span>
      </a>
      @endforeach
    </div>

    <!-- Provider tabs -->
    @if($providers->count())
    <div class="provider-tabs">
      <a href="{{ request()->fullUrlWithQuery(['provider' => null, 'page' => null]) }}"
         class="provider-tab {{ !request('provider') ? 'active' : '' }}">
        {{ __('models.filters.all') }}
      </a>
      @foreach($providers->take(15) as $p)
      <a href="{{ request()->fullUrlWithQuery(['provider' => $p->provider, 'page' => null]) }}"
         class="provider-tab {{ request('provider') === $p->provider ? 'active' : '' }}">
        {{ ucfirst($p->provider) }}
        <span class="pt-count">· {{ $p->count }}</span>
      </a>
      @endforeach
    </div>
    @endif

    <!-- Models grid -->
    @if($models->isEmpty())
      <div class="empty-state">
        <span class="material-icons-round">search_off</span>
        <h3>{{ __('models.empty.title') }}</h3>
        <p>{{ __('models.empty.subtitle') }}</p>
        <a href="{{ route('models.index') }}" class="btn btn-primary" style="margin-top:16px">
          <span class="material-icons-round">refresh</span>
          {{ __('models.empty.reset') }}
        </a>
      </div>
    @else
      <div class="models-grid">
        @foreach($models as $model)
          @include('models.partials.tile', ['model' => $model])
        @endforeach
      </div>

      @if($models->hasPages())
        <div class="pagination-wrap">
          {{ $models->links('vendor.pagination.cloudapi') }}
        </div>
      @endif
    @endif
  </form>
</div>

@endsection

@push('scripts')
<script>
let searchTimer;
document.querySelector('.search-input')?.addEventListener('input', function() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => this.form.submit(), 500);
});
</script>
@endpush