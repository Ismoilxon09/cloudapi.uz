<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'CloudAPI — Unified AI API Gateway')</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="{{ asset('brand/favicon.svg') }}">
<link rel="apple-touch-icon" href="{{ asset('brand/favicon.svg') }}">
<meta name="theme-color" content="#0A0A0A">

<style>
:root {
  --ease: cubic-bezier(.32, .72, 0, 1);
  --ease-spring: cubic-bezier(.16, 1, .3, 1);
  --r: 12px;
  --r-sm: 8px;
  --r-lg: 16px;
}

[data-theme="light"] {
  --bg: #FFFFFF;
  --bg-elevated: #FFFFFF;
  --bg-subtle: #F9FAFB;
  --bg-hover: #F3F4F6;
  --border: #E5E7EB;
  --border-strong: #D1D5DB;
  --text: #111111;
  --text-strong: #000000;
  --text-muted: #6B7280;
  --text-subtle: #9CA3AF;
  --gray-deep: #374151;
  --primary: #111111;
  --primary-hover: #000000;
  --accent: #2563EB;
  --accent-hover: #1D4ED8;
  --success: #10B981;
  --warning: #F59E0B;
  --danger: #EF4444;
  --shadow-sm: 0 1px 2px rgba(17, 17, 17, .04);
  --shadow: 0 1px 3px rgba(17, 17, 17, .06);
  --shadow-md: 0 8px 24px rgba(17, 17, 17, .06);
  --shadow-lg: 0 20px 48px rgba(17, 17, 17, .08);
}

[data-theme="dark"] {
  --bg: #050505;
  --bg-elevated: #141414;
  --bg-subtle: #1A1A1A;
  --bg-hover: #242424;
  --border: #2A2A2A;
  --border-strong: #3F3F3F;
  --text: #F9FAFB;
  --text-strong: #FFFFFF;
  --text-muted: #B0B0B8;
  --text-subtle: #7A7A85;
  --gray-deep: #E5E7EB;
  --primary: #FFFFFF;
  --primary-hover: #F3F4F6;
  --accent: #3B82F6;
  --accent-hover: #60A5FA;
  --success: #10B981;
  --warning: #F59E0B;
  --danger: #EF4444;
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, .5);
  --shadow: 0 1px 3px rgba(0, 0, 0, .6);
  --shadow-md: 0 8px 24px rgba(0, 0, 0, .5);
  --shadow-lg: 0 20px 48px rgba(0, 0, 0, .7);
}

* { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
html { scroll-behavior: smooth; }
html, body {
  font-family: 'Inter', -apple-system, system-ui, sans-serif;
  background: var(--bg);
  color: var(--text);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-feature-settings: 'cv11', 'ss01', 'cv02';
  transition: background-color .3s var(--ease), color .3s var(--ease);
  overflow-x: hidden;
}
a { color: inherit; text-decoration: none; }
button { font-family: inherit; cursor: pointer; border: none; background: none; color: inherit; }
input, textarea, select { font-family: inherit; color: inherit; }
code, pre, .mono { font-family: 'JetBrains Mono', 'SF Mono', Menlo, monospace; }

/* Authenticated layout uses sidebar */
body.has-sidebar { display: flex; min-height: 100vh; }
body.has-sidebar .cloud-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }

.brand {
  display: flex; align-items: center; gap: 10px;
  font-weight: 700; font-size: 15px; letter-spacing: -0.02em;
}
.brand-mark {
  width: 30px; height: 30px;
  display: flex; align-items: center; justify-content: center;
  color: var(--text-strong);
}
.brand-mark svg { width: 100%; height: 100%; fill: currentColor; }
.brand-mark .material-icons-round { font-size: 18px; }

.nav-links { display: flex; align-items: center; gap: 2px; }
.nav-link {
  padding: 7px 14px; font-size: 13px; font-weight: 500;
  color: var(--text-muted); border-radius: var(--r-sm);
  transition: all .15s var(--ease);
}
.nav-link:hover { background: var(--bg-subtle); color: var(--text); }
.nav-link.active { background: var(--bg-subtle); color: var(--text); font-weight: 600; }

.topbar-actions { display: flex; align-items: center; gap: 8px; }

.icon-btn {
  width: 36px; height: 36px; border-radius: var(--r-sm);
  display: flex; align-items: center; justify-content: center;
  color: var(--text-muted); transition: all .15s var(--ease);
}
.icon-btn:hover { background: var(--bg-subtle); color: var(--text); }
.icon-btn .material-icons-round { font-size: 20px; }

/* Lang switcher (guest only) */
.lang-switcher { position: relative; }
.lang-btn {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 12px; font-size: 12px; font-weight: 600;
  color: var(--text-muted); border: 1px solid var(--border);
  border-radius: var(--r-sm); background: var(--bg-elevated);
  transition: all .15s var(--ease);
  text-transform: uppercase; letter-spacing: 0.04em;
}
.lang-btn:hover { border-color: var(--border-strong); color: var(--text); }
.lang-btn .material-icons-round { font-size: 14px; }
.lang-dropdown {
  position: absolute; top: calc(100% + 6px); right: 0;
  min-width: 160px;
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: var(--r); box-shadow: var(--shadow-lg);
  padding: 4px; opacity: 0; pointer-events: none;
  transform: translateY(-4px); transition: all .15s var(--ease);
  z-index: 60;
}
.lang-switcher.open .lang-dropdown { opacity: 1; pointer-events: all; transform: translateY(0); }
.lang-option {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 12px; font-size: 13px;
  border-radius: 6px; cursor: pointer;
}
.lang-option:hover { background: var(--bg-subtle); }
.lang-option.active { background: var(--bg-subtle); font-weight: 600; }
.lang-flag { font-size: 16px; line-height: 1; }

/* Buttons */
.btn {
  display: inline-flex; align-items: center; justify-content: center;
  gap: 6px; padding: 9px 16px;
  font-size: 13px; font-weight: 500;
  border-radius: var(--r-sm); border: 1px solid transparent;
  transition: all .15s var(--ease);
  white-space: nowrap; position: relative;
}
.btn:active { transform: scale(.98); }
.btn .material-icons-round { font-size: 16px; }
.btn-primary { background: var(--primary); color: var(--bg-elevated); }
.btn-primary:hover { background: var(--primary-hover); box-shadow: 0 8px 24px rgba(17, 17, 17, .15); }
.btn-accent { background: var(--accent); color: white; }
.btn-accent:hover { background: var(--accent-hover); box-shadow: 0 8px 24px rgba(37, 99, 235, .25); }
.btn-secondary {
  background: var(--bg-elevated); color: var(--text);
  border-color: var(--border);
}
.btn-secondary:hover { background: var(--bg-subtle); border-color: var(--border-strong); }
.btn-ghost { background: transparent; color: var(--text-muted); }
.btn-ghost:hover { background: var(--bg-subtle); color: var(--text); }
.btn-lg { padding: 13px 22px; font-size: 14px; }
.btn-sm { padding: 6px 10px; font-size: 12px; }
.btn-sm .material-icons-round { font-size: 14px; }

/* Cards / inputs / tables */
.card {
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: var(--r); padding: 20px;
}
.input, .textarea, .select {
  width: 100%; padding: 10px 12px; font-size: 13px;
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: var(--r-sm); outline: none;
  transition: all .15s var(--ease);
}
.input:focus, .textarea:focus, .select:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}
.label { display: block; font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; }
.field { margin-bottom: 16px; }

.badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 2px 8px; font-size: 11px; font-weight: 500;
  border-radius: 99px;
  background: var(--bg-subtle); color: var(--text-muted);
}
.badge-success { background: rgba(16, 185, 129, .1); color: var(--success); }
.badge-warning { background: rgba(245, 158, 11, .1); color: var(--warning); }
.badge-danger  { background: rgba(239, 68, 68, .1); color: var(--danger); }
.badge-info    { background: rgba(37, 99, 235, .1); color: var(--accent); }

.alert {
  display: flex; align-items: flex-start; gap: 10px;
  padding: 12px 14px; border-radius: var(--r-sm);
  font-size: 13px; border: 1px solid; margin-bottom: 16px;
}
.alert .material-icons-round { font-size: 18px; flex-shrink: 0; }
.alert-success { background: rgba(16, 185, 129, .08); border-color: rgba(16, 185, 129, .25); color: var(--success); }
.alert-warning { background: rgba(245, 158, 11, .08); border-color: rgba(245, 158, 11, .25); color: var(--warning); }
.alert-danger  { background: rgba(239, 68, 68, .08); border-color: rgba(239, 68, 68, .25); color: var(--danger); }
.alert-info    { background: rgba(37, 99, 235, .08); border-color: rgba(37, 99, 235, .25); color: var(--accent); }

.page-header { margin-bottom: 28px; }
.page-title { font-size: 24px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); }

.flex { display: flex; }
.flex-col { flex-direction: column; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.gap-4 { gap: 16px; }
.mt-2 { margin-top: 8px; }
.mt-4 { margin-top: 16px; }
.mb-4 { margin-bottom: 16px; }
.text-center { text-align: center; }
.text-muted { color: var(--text-muted); }
.w-full { width: 100%; }

@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.fade-up { animation: fadeUp .6s var(--ease-spring) both; }
.fade-in { animation: fadeIn .5s var(--ease) both; }

/* ===== LANDING NAV (guest pages) ===== */
.landing-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: rgba(255, 255, 255, .72);
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
  border-bottom: 1px solid var(--border);
  height: 64px;
  display: flex;
  align-items: center;
  padding: 0 32px;
}
[data-theme="dark"] .landing-nav { background: rgba(10, 10, 10, .72); }

.landing-nav-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  gap: 24px;
}

@media (max-width: 768px) {
  .landing-nav { padding: 0 16px; }
  .landing-nav .nav-links { display: none; }
}
</style>

@auth
  @include('partials.sidebar-styles')
@endauth

@stack('styles')
</head>
<body @auth class="has-sidebar" @endauth>

@auth
  @include('partials.sidebar')
  <div class="cloud-main">
    @include('partials.header')
    <main>
      @yield('content')
    </main>
  </div>
@endauth

@guest
  <main>
    @yield('content')
  </main>
@endguest

@if(session('success'))
<div class="alert alert-success fade-up" style="position:fixed;bottom:20px;right:20px;max-width:380px;box-shadow:var(--shadow-lg);z-index:100">
  <span class="material-icons-round">check_circle</span>
  <div>{{ session('success') }}</div>
</div>
@endif

<script>
function toggleTheme() {
  const root = document.documentElement;
  const newTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
  root.dataset.theme = newTheme;
  localStorage.setItem('theme', newTheme);
  const ic = document.getElementById('themeIcon');
  if(ic) ic.textContent = newTheme === 'dark' ? 'light_mode' : 'dark_mode';
}

(function() {
  const saved = localStorage.getItem('theme') || 'light';
  document.documentElement.dataset.theme = saved;
  const ic = document.getElementById('themeIcon');
  if(ic) ic.textContent = saved === 'dark' ? 'light_mode' : 'dark_mode';
})();

document.addEventListener('click', (e) => {
  document.querySelectorAll('.lang-switcher.open').forEach(m => {
    if(!m.contains(e.target)) m.classList.remove('open');
  });
});

setTimeout(() => {
  document.querySelectorAll('.alert[style*="position:fixed"]').forEach(a => {
    a.style.opacity = '0';
    a.style.transition = 'opacity .3s';
    setTimeout(() => a.remove(), 300);
  });
}, 4000);
</script>

@stack('scripts')

</body>
</html>