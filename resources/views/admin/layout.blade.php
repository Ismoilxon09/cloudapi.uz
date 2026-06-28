<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin') — CloudAPI</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<style>
:root {
  --ease: cubic-bezier(.32, .72, 0, 1);
  --ease-spring: cubic-bezier(.16, 1, .3, 1);
  --sidebar-width: 240px;
  --sidebar-width-collapsed: 64px;
  --header-height: 60px;
}

[data-theme="light"] {
  --bg: #F8FAFC;
  --bg-elevated: #FFFFFF;
  --bg-subtle: #F1F5F9;
  --bg-hover: #E2E8F0;
  --border: #E2E8F0;
  --border-strong: #CBD5E1;
  --text: #1E293B;
  --text-strong: #0F172A;
  --text-muted: #64748B;
  --text-subtle: #94A3B8;
  --primary: #0F172A;
  --primary-hover: #1E293B;
  --accent: #2563EB;
  --accent-hover: #1D4ED8;
  --success: #10B981;
  --warning: #F59E0B;
  --danger: #EF4444;
  --info: #3B82F6;
  --shadow-sm: 0 1px 2px rgba(15, 23, 42, .04);
  --shadow: 0 1px 3px rgba(15, 23, 42, .08);
  --shadow-md: 0 8px 24px rgba(15, 23, 42, .08);
  --shadow-lg: 0 20px 48px rgba(15, 23, 42, .12);
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
  --primary: #FFFFFF;
  --primary-hover: #F3F4F6;
  --accent: #3B82F6;
  --accent-hover: #60A5FA;
  --success: #10B981;
  --warning: #F59E0B;
  --danger: #EF4444;
  --info: #3B82F6;
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, .5);
  --shadow: 0 1px 3px rgba(0, 0, 0, .6);
  --shadow-md: 0 8px 24px rgba(0, 0, 0, .5);
  --shadow-lg: 0 20px 48px rgba(0, 0, 0, .7);
}

* { margin: 0; padding: 0; box-sizing: border-box; }
html, body {
  font-family: 'Inter', -apple-system, system-ui, sans-serif;
  background: var(--bg);
  color: var(--text);
  -webkit-font-smoothing: antialiased;
  font-feature-settings: 'cv11', 'ss01';
}

a { color: inherit; text-decoration: none; }
button { font-family: inherit; cursor: pointer; border: none; background: none; color: inherit; }
input, textarea, select { font-family: inherit; color: inherit; }
code, .mono { font-family: 'JetBrains Mono', monospace; }

body.admin-layout {
  display: flex;
  min-height: 100vh;
}

/* ========== ADMIN SIDEBAR ========== */
.admin-sidebar {
  width: var(--sidebar-width);
  flex-shrink: 0;
  background: var(--bg-elevated);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  height: 100vh;
  z-index: 50;
  transition: width .25s var(--ease);
}

.admin-sidebar.collapsed { width: var(--sidebar-width-collapsed); }

body.admin-layout { padding-left: var(--sidebar-width); transition: padding-left .25s var(--ease); }
body.admin-layout.sidebar-collapsed { padding-left: var(--sidebar-width-collapsed); }

.adm-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  height: var(--header-height);
  border-bottom: 1px solid var(--border);
}

.adm-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  font-size: 15px;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  overflow: hidden;
}

.adm-brand-mark {
  width: 30px;
  height: 30px;
  background: var(--danger);
  color: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.adm-brand-mark .material-icons-round { font-size: 18px; }

.adm-brand-text { white-space: nowrap; transition: opacity .2s; }
.admin-sidebar.collapsed .adm-brand-text { opacity: 0; pointer-events: none; }

.adm-collapse-btn {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: 1px solid var(--border);
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
}

.adm-collapse-btn:hover { background: var(--bg-subtle); color: var(--text); border-color: var(--border-strong); }
.adm-collapse-btn .material-icons-round { font-size: 16px; transition: transform .25s; }
.admin-sidebar.collapsed .adm-collapse-btn { margin: 0 auto; }
.admin-sidebar.collapsed .adm-collapse-btn .material-icons-round { transform: rotate(180deg); }

.adm-nav {
  flex: 1;
  padding: 8px 8px;
  overflow-y: auto;
}

.adm-nav::-webkit-scrollbar { width: 4px; }
.adm-nav::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 99px; }

.adm-nav-group { margin-bottom: 14px; }

.adm-nav-label {
  font-size: 10px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 8px 10px 6px;
  white-space: nowrap;
}

.admin-sidebar.collapsed .adm-nav-label { opacity: 0; height: 0; padding: 0; margin-bottom: 0; overflow: hidden; }
.admin-sidebar.collapsed .adm-nav-group { margin-bottom: 4px; }

.adm-nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 10px;
  margin-bottom: 1px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
  border-radius: 8px;
  transition: all .15s;
  position: relative;
}

.adm-nav-item:hover { background: var(--bg-subtle); color: var(--text-strong); }

.adm-nav-item.active {
  background: var(--text-strong);
  color: var(--bg);
  font-weight: 600;
}

.adm-nav-item.active .adm-nav-icon { color: var(--bg); }

.adm-nav-icon { font-size: 18px !important; flex-shrink: 0; color: var(--text-muted); }
.adm-nav-item:hover .adm-nav-icon { color: var(--text-strong); }

.adm-nav-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }

.adm-nav-badge {
  background: var(--danger);
  color: white;
  font-size: 10px;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 99px;
  font-family: 'JetBrains Mono', monospace;
  min-width: 18px;
  text-align: center;
}

.adm-nav-item.active .adm-nav-badge { background: white; color: var(--text-strong); }

.admin-sidebar.collapsed .adm-nav-text,
.admin-sidebar.collapsed .adm-nav-badge { display: none; }

.admin-sidebar.collapsed .adm-nav-item { justify-content: center; padding: 10px; }

.admin-sidebar.collapsed .adm-nav-item:hover::after {
  content: attr(title);
  position: absolute;
  left: calc(100% + 12px);
  top: 50%;
  transform: translateY(-50%);
  background: var(--text-strong);
  color: var(--bg);
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 12px;
  white-space: nowrap;
  z-index: 100;
  box-shadow: var(--shadow-md);
}

.adm-bottom { padding: 8px; border-top: 1px solid var(--border); }

/* ========== ADMIN HEADER ========== */
.admin-main {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.admin-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 24px;
  height: var(--header-height);
  background: var(--bg-elevated);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 40;
  flex-shrink: 0;
}

.adm-h-title { font-size: 14px; font-weight: 600; color: var(--text-strong); }

.adm-h-tools { display: flex; align-items: center; gap: 6px; margin-left: auto; }

.adm-h-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--bg-elevated);
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  cursor: pointer;
}

.adm-h-btn:hover { background: var(--bg-subtle); color: var(--text); border-color: var(--border-strong); }
.adm-h-btn .material-icons-round { font-size: 18px; }

.adm-h-btn-dot {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--danger);
  border: 2px solid var(--bg-elevated);
}

.adm-h-user {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 8px 4px 4px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--bg-elevated);
  cursor: pointer;
}

.adm-h-user:hover { background: var(--bg-subtle); }

.adm-h-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--danger);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
}

.adm-h-user-info { display: flex; flex-direction: column; }
.adm-h-user-name { font-size: 12px; font-weight: 600; color: var(--text-strong); line-height: 1.2; }
.adm-h-user-role {
  font-size: 9px;
  color: var(--danger);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-weight: 700;
  line-height: 1.2;
}

/* ========== COMMON COMPONENTS ========== */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 9px 16px;
  font-size: 13px;
  font-weight: 500;
  border-radius: 8px;
  border: 1px solid transparent;
  transition: all .15s;
  white-space: nowrap;
}

.btn .material-icons-round { font-size: 16px; }
.btn-primary { background: var(--primary); color: var(--bg-elevated); }
.btn-primary:hover { background: var(--primary-hover); }
.btn-secondary { background: var(--bg-elevated); color: var(--text); border-color: var(--border); }
.btn-secondary:hover { background: var(--bg-subtle); border-color: var(--border-strong); }
.btn-success { background: var(--success); color: white; }
.btn-success:hover { background: #059669; }
.btn-danger { background: var(--danger); color: white; }
.btn-danger:hover { background: #DC2626; }
.btn-ghost { background: transparent; color: var(--text-muted); }
.btn-ghost:hover { background: var(--bg-subtle); color: var(--text); }
.btn-sm { padding: 5px 10px; font-size: 12px; }
.btn-sm .material-icons-round { font-size: 14px; }

.card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
}

.input, .textarea, .select {
  width: 100%;
  padding: 9px 12px;
  font-size: 13px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 8px;
  outline: none;
  transition: all .15s;
}

.input:focus, .textarea:focus, .select:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}

.label { display: block; font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; }
.field { margin-bottom: 16px; }

.badge {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 2px 8px;
  font-size: 11px;
  font-weight: 600;
  border-radius: 99px;
  background: var(--bg-subtle);
  color: var(--text-muted);
}

.badge-success { background: rgba(16, 185, 129, .12); color: var(--success); }
.badge-warning { background: rgba(245, 158, 11, .12); color: var(--warning); }
.badge-danger { background: rgba(239, 68, 68, .12); color: var(--danger); }
.badge-info { background: rgba(37, 99, 235, .12); color: var(--accent); }

.alert {
  display: flex;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 8px;
  font-size: 13px;
  border: 1px solid;
  margin-bottom: 16px;
}

.alert-success { background: rgba(16, 185, 129, .08); border-color: rgba(16, 185, 129, .25); color: var(--success); }
.alert-danger { background: rgba(239, 68, 68, .08); border-color: rgba(239, 68, 68, .25); color: var(--danger); }
.alert-info { background: rgba(37, 99, 235, .08); border-color: rgba(37, 99, 235, .25); color: var(--accent); }

.alert .material-icons-round { font-size: 18px; flex-shrink: 0; }

/* Page header */
.page-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.page-title { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-strong); margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-muted); }

.w-full { width: 100%; }
.mt-2 { margin-top: 8px; }
.mt-4 { margin-top: 16px; }
.mb-4 { margin-bottom: 16px; }

/* ========== PAGINATION ========== */
.adm-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  flex-wrap: wrap;
  gap: 12px;
}

.adm-pag-info {
  font-size: 12px;
  color: var(--text);
  font-family: 'JetBrains Mono', monospace;
}

.adm-pag-info strong {
  color: var(--text-strong);
  font-weight: 700;
}

.adm-pag-btns {
  display: flex;
  align-items: center;
  gap: 4px;
}

.adm-pag-btn {
  min-width: 32px;
  height: 32px;
  padding: 0 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 7px;
  text-decoration: none;
  transition: all .15s;
  font-family: 'JetBrains Mono', monospace;
}

.adm-pag-btn:hover {
  background: var(--bg-subtle);
  color: var(--text-strong);
  border-color: var(--border-strong);
}

.adm-pag-btn.active {
  background: var(--text-strong);
  color: var(--bg-elevated);
  border-color: var(--text-strong);
}

.adm-pag-btn.disabled {
  opacity: 0.4;
  cursor: not-allowed;
  pointer-events: none;
}

.adm-pag-btn .material-icons-round {
  font-size: 16px;
}

.adm-pag-dots {
  padding: 0 4px;
  color: var(--text-subtle);
  font-size: 13px;
}

@media (max-width: 768px) {
  .adm-pagination { flex-direction: column-reverse; }
  .adm-pag-btn { min-width: 28px; height: 28px; font-size: 11px; }
}

@media (max-width: 768px) {
  body.admin-layout { padding-left: 0; }
  body.admin-layout.sidebar-collapsed { padding-left: 0; }
  .admin-sidebar { left: -260px; transition: left .25s; }
  .admin-sidebar.mobile-open { left: 0; }
}
</style>

@stack('styles')
</head>
<body class="admin-layout">

@include('admin.partials.sidebar')

<div class="admin-main">
  <!-- Header -->
  <header class="admin-header">
    <div class="adm-h-title">@yield('page_title', 'Admin Panel')</div>

    <div class="adm-h-tools">
      <!-- Notifications -->
      <a href="{{ route('admin.notifications.index') }}" class="adm-h-btn" title="Bildirishnomalar">
        <span class="material-icons-round">notifications</span>
        @php $unread = \App\Models\AdminNotification::whereNull('read_at')->count(); @endphp
        @if($unread > 0)<span class="adm-h-btn-dot"></span>@endif
      </a>

      <!-- Theme -->
      <button class="adm-h-btn" onclick="toggleTheme()" title="Theme">
        <span class="material-icons-round" id="themeIcon">dark_mode</span>
      </button>

      <!-- User -->
      <div class="adm-h-user">
        <div class="adm-h-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <div class="adm-h-user-info">
          <div class="adm-h-user-name">{{ auth()->user()->name }}</div>
          <div class="adm-h-user-role">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
        </div>
      </div>
    </div>
  </header>

  <main style="flex:1">
    @yield('content')
  </main>
</div>

@if(session('success'))
<div class="alert alert-success" style="position:fixed;bottom:20px;right:20px;max-width:380px;box-shadow:var(--shadow-lg);z-index:100">
  <span class="material-icons-round">check_circle</span>
  <div>{{ session('success') }}</div>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger" style="position:fixed;bottom:20px;right:20px;max-width:380px;box-shadow:var(--shadow-lg);z-index:100">
  <span class="material-icons-round">error</span>
  <div>{{ session('error') }}</div>
</div>
@endif

<script>
function toggleTheme() {
  const t = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
  document.documentElement.dataset.theme = t;
  localStorage.setItem('admin-theme', t);
  document.getElementById('themeIcon').textContent = t === 'dark' ? 'light_mode' : 'dark_mode';
}

(function() {
  const saved = localStorage.getItem('admin-theme') || 'light';
  document.documentElement.dataset.theme = saved;
  document.getElementById('themeIcon').textContent = saved === 'dark' ? 'light_mode' : 'dark_mode';
})();

function toggleAdminSidebar() {
  const sb = document.getElementById('adminSidebar');
  sb.classList.toggle('collapsed');
  const collapsed = sb.classList.contains('collapsed');
  document.body.classList.toggle('sidebar-collapsed', collapsed);
  localStorage.setItem('admin-sidebar-collapsed', collapsed ? '1' : '0');
}

(function() {
  if (localStorage.getItem('admin-sidebar-collapsed') === '1') {
    document.getElementById('adminSidebar').classList.add('collapsed');
    document.body.classList.add('sidebar-collapsed');
  }
})();

// Auto-fade flash alerts
setTimeout(() => {
  document.querySelectorAll('.alert[style*="position:fixed"]').forEach(a => {
    a.style.opacity = '0';
    a.style.transition = 'opacity .3s';
    setTimeout(() => a.remove(), 300);
  });
}, 4000);

// Auto-refresh notifications count every 30s
setInterval(async () => {
  try {
    const res = await fetch('{{ route('admin.notifications.unread') }}', { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    const dot = document.querySelector('.adm-h-btn-dot');
    if (data.count > 0 && !dot) {
      const btn = document.querySelector('.admin-header .adm-h-btn[title="Bildirishnomalar"]');
      if (btn) {
        const newDot = document.createElement('span');
        newDot.className = 'adm-h-btn-dot';
        btn.appendChild(newDot);
      }
    }
  } catch (e) {}
}, 30000);
</script>

@stack('scripts')
</body>
</html>