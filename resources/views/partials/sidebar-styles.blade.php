<style>
:root {
  --sidebar-width: 240px;
  --sidebar-width-collapsed: 64px;
  --header-height: 60px;
}

body.has-sidebar {
  min-height: 100vh;
  overflow-x: hidden;
  padding-left: var(--sidebar-width);
  transition: padding-left .25s var(--ease);
}

body.has-sidebar.sidebar-collapsed {
  padding-left: var(--sidebar-width-collapsed);
}

/* ===== SIDEBAR ===== */
.cloud-sidebar {
  width: var(--sidebar-width);
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

.cloud-sidebar.collapsed { width: var(--sidebar-width-collapsed); }

.cs-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  height: var(--header-height);
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
}

.cs-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  font-size: 15px;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  text-decoration: none;
  overflow: hidden;
}

.cs-brand-mark {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--text-strong);
}

.cs-brand-mark svg { width: 100%; height: 100%; fill: currentColor; }
.cs-brand-mark .material-icons-round { font-size: 18px; }

.cs-brand-text {
  white-space: nowrap;
  transition: opacity .2s;
}

.cloud-sidebar.collapsed .cs-brand-text { opacity: 0; pointer-events: none; }

.cs-collapse-btn {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .15s;
  flex-shrink: 0;
}

.cs-collapse-btn:hover {
  background: var(--bg-subtle);
  color: var(--text);
  border-color: var(--border-strong);
}

.cs-collapse-btn .material-icons-round { font-size: 16px; transition: transform .25s; }
.cloud-sidebar.collapsed .cs-collapse-btn { margin: 0 auto; }
.cloud-sidebar.collapsed .cs-collapse-btn .material-icons-round { transform: rotate(180deg); }

.cs-nav {
  flex: 1;
  padding: 8px 8px;
  overflow-y: auto;
}

.cs-nav::-webkit-scrollbar { width: 4px; }
.cs-nav::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 99px; }

.cs-nav-group { margin-bottom: 14px; }

.cs-nav-label {
  font-size: 10px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 8px 10px 6px;
  white-space: nowrap;
}

.cloud-sidebar.collapsed .cs-nav-label {
  opacity: 0;
  height: 0;
  padding: 0;
  margin-bottom: 0;
  overflow: hidden;
}

.cloud-sidebar.collapsed .cs-nav-group { margin-bottom: 4px; }

.cs-nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 10px;
  margin-bottom: 1px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
  border-radius: 8px;
  text-decoration: none;
  transition: all .15s;
  position: relative;
}

.cs-nav-item:hover {
  background: var(--bg-subtle);
  color: var(--text-strong);
}

[data-theme="dark"] .cs-nav-item:hover {
  background: var(--bg-hover);
}

.cs-nav-item.active {
  background: var(--text-strong);
  color: var(--bg);
  font-weight: 600;
}

.cs-nav-item.active .cs-nav-icon { color: var(--bg); }

.cs-nav-icon {
  font-size: 18px !important;
  color: var(--text-muted);
  flex-shrink: 0;
}

.cs-nav-item:hover .cs-nav-icon { color: var(--text-strong); }

.cs-nav-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
}

.cloud-sidebar.collapsed .cs-nav-item {
  justify-content: center;
  padding: 10px;
}

.cloud-sidebar.collapsed .cs-nav-text { display: none; }

.cloud-sidebar.collapsed .cs-nav-item:hover::after {
  content: attr(title);
  position: absolute;
  left: calc(100% + 12px);
  top: 50%;
  transform: translateY(-50%);
  background: var(--text-strong);
  color: var(--bg-elevated);
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 12px;
  white-space: nowrap;
  z-index: 100;
  pointer-events: none;
  box-shadow: var(--shadow-md);
}

.cs-bottom {
  padding: 8px;
  border-top: 1px solid var(--border);
  flex-shrink: 0;
}

/* ===== MAIN AREA ===== */
.cloud-main {
  min-width: 0;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* ===== HEADER ===== */
.cloud-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 20px;
  height: var(--header-height);
  background: rgba(255, 255, 255, .85);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 40;
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  flex-shrink: 0;
}

[data-theme="dark"] .cloud-header { background: rgba(10, 10, 10, .85); }

.ch-page-info { font-size: 14px; font-weight: 600; color: var(--text-strong); }

.ch-tools {
  display: flex;
  align-items: center;
  gap: 6px;
}

.ch-divider {
  width: 1px;
  height: 24px;
  background: var(--border);
  margin: 0 4px;
}

/* Balance card */
.ch-balance {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 12px 6px 8px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: var(--bg-subtle);
  text-decoration: none;
  color: inherit;
  transition: all .15s;
}

.ch-balance:hover {
  border-color: var(--border-strong);
  background: var(--bg-hover);
}

.ch-balance-icon {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  background: var(--primary);
  color: var(--bg-elevated);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.ch-balance-icon .material-icons-round { font-size: 14px; }

.ch-balance-info { display: flex; flex-direction: column; gap: 0; }

.ch-balance-label {
  font-size: 9px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-subtle);
  font-weight: 700;
  line-height: 1;
}

.ch-balance-value {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-strong);
  font-family: 'JetBrains Mono', monospace;
  line-height: 1.2;
}

.ch-balance-currency {
  font-size: 10px;
  color: var(--text-muted);
  font-weight: 500;
  margin-left: 1px;
}

/* Top up button */
.ch-topup-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: var(--primary);
  color: var(--bg-elevated);
  border-radius: 10px;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  transition: all .15s;
  border: 1px solid var(--primary);
}

.ch-topup-btn:hover {
  background: var(--primary-hover);
  box-shadow: 0 4px 12px rgba(17,17,17,.12);
}

.ch-topup-btn .material-icons-round { font-size: 16px; }

/* Icon buttons */
.ch-icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  transition: all .15s;
  font-size: 12px;
  font-weight: 600;
}

.ch-icon-btn:hover {
  background: var(--bg-subtle);
  color: var(--text);
  border-color: var(--border-strong);
}

.ch-icon-btn .material-icons-round { font-size: 18px; }

/* ============ NOTIFICATIONS BELL ============ */
.ch-notif-wrap {
  position: relative;
}

.ch-notif-btn {
  position: relative;
}

.ch-notif-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  min-width: 16px;
  height: 16px;
  padding: 0 4px;
  background: #EF4444;
  color: white;
  font-size: 10px;
  font-weight: 700;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--bg);
  line-height: 1;
  letter-spacing: -.02em;
  animation: badgePop .3s ease-out;
}

@keyframes badgePop {
  from { transform: scale(0); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.ch-notif-menu {
  width: 360px;
  max-height: 480px;
  padding: 0 !important;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.ch-notif-header {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.ch-notif-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-strong);
}

.ch-notif-markall {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .15s;
}

.ch-notif-markall:hover {
  background: var(--bg-subtle);
  color: var(--text-strong);
}

.ch-notif-markall .material-icons-round { font-size: 16px; }

.ch-notif-body {
  flex: 1;
  overflow-y: auto;
  max-height: 360px;
}

.ch-notif-loading,
.ch-notif-empty {
  padding: 32px 16px;
  text-align: center;
  color: var(--text-muted);
  font-size: 13px;
}

.ch-notif-loading .material-icons-round,
.ch-notif-empty .material-icons-round {
  font-size: 32px;
  color: var(--text-subtle);
  display: block;
  margin: 0 auto 8px;
}

.ch-notif-item {
  display: flex;
  gap: 10px;
  padding: 11px 14px;
  border-bottom: 1px solid var(--border);
  text-decoration: none;
  color: inherit;
  cursor: pointer;
  transition: background .12s;
  position: relative;
}

.ch-notif-item:hover {
  background: var(--bg-subtle);
}

.ch-notif-item:last-child {
  border-bottom: none;
}

.ch-notif-item.unread {
  background: rgba(37,99,235,.04);
}

.ch-notif-item.unread::before {
  content: '';
  position: absolute;
  left: 5px;
  top: 50%;
  transform: translateY(-50%);
  width: 6px;
  height: 6px;
  background: var(--accent);
  border-radius: 50%;
}

.ch-notif-item-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.ch-notif-item-icon .material-icons-round { font-size: 18px; }

.ch-notif-item.notif-success .ch-notif-item-icon { background: rgba(16,185,129,.12); color: #10B981; }
.ch-notif-item.notif-warning .ch-notif-item-icon { background: rgba(245,158,11,.12); color: #F59E0B; }
.ch-notif-item.notif-info .ch-notif-item-icon { background: rgba(34,158,217,.12); color: #229ED9; }
.ch-notif-item.notif-primary .ch-notif-item-icon { background: rgba(37,99,235,.12); color: #2563EB; }
.ch-notif-item.notif-default .ch-notif-item-icon { background: var(--bg-subtle); color: var(--text-muted); }

.ch-notif-item-content {
  flex: 1;
  min-width: 0;
}

.ch-notif-item-title {
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-strong);
  margin-bottom: 2px;
}

.ch-notif-item-msg {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.ch-notif-item-time {
  font-size: 10.5px;
  color: var(--text-subtle);
  margin-top: 4px;
  display: block;
}

.ch-notif-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 11px;
  border-top: 1px solid var(--border);
  background: var(--bg-subtle);
  color: var(--accent);
  font-size: 12.5px;
  font-weight: 600;
  text-decoration: none;
  transition: all .15s;
}

.ch-notif-footer:hover {
  background: var(--bg-elevated);
}

.ch-notif-footer .material-icons-round { font-size: 14px; }

@media (max-width: 640px) {
  .ch-notif-menu {
    width: calc(100vw - 32px);
    max-width: 340px;
    right: -8px;
  }
}

.ch-lang-btn {
  width: auto;
  padding: 0 10px;
  letter-spacing: 0.04em;
}

/* User button */
.ch-user-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.ch-user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--primary);
  color: var(--bg-elevated);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  transition: transform .15s;
}

.ch-user-btn:hover .ch-user-avatar {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0,0,0,.15);
}

/* Dropdowns */
.ch-dropdown { position: relative; }

.ch-dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 200px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: var(--shadow-lg);
  padding: 6px;
  opacity: 0;
  pointer-events: none;
  transform: translateY(-4px);
  transition: all .15s;
  z-index: 60;
}

.ch-dropdown.open .ch-dropdown-menu {
  opacity: 1;
  pointer-events: all;
  transform: translateY(0);
}

.ch-user-menu { min-width: 280px; }

.ch-user-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px;
  border-bottom: 1px solid var(--border);
  margin-bottom: 6px;
}

.ch-user-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-strong);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ch-user-email {
  font-size: 11px;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ch-user-balance {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px;
  margin: 0 -6px;
  background: var(--bg-subtle);
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  margin-bottom: 6px;
}

.ch-dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  font-size: 13px;
  color: var(--text);
  border-radius: 6px;
  text-decoration: none;
  border: none;
  background: transparent;
  cursor: pointer;
  font-family: inherit;
}

.ch-dropdown-item:hover { background: var(--bg-subtle); }
.ch-dropdown-item.active { background: var(--bg-subtle); font-weight: 600; }
.ch-dropdown-item .material-icons-round { font-size: 16px; color: var(--text-muted); }

.ch-dropdown-divider {
  height: 1px;
  background: var(--border);
  margin: 4px 0;
}

/* Mobile */
.ch-mobile-toggle {
  display: none;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text);
  cursor: pointer;
  align-items: center;
  justify-content: center;
}

.ch-mobile-toggle .material-icons-round { font-size: 20px; }

.cs-mobile-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, .5);
  z-index: 45;
}

@media (max-width: 1024px) {
  .ch-balance-info { display: none; }
  .ch-balance { padding: 6px; }
  .ch-topup-text { display: none; }
  .ch-topup-btn { padding: 8px; }
}

@media (max-width: 768px) {
  body.has-sidebar { padding-left: 0; }
  body.has-sidebar.sidebar-collapsed { padding-left: 0; }
  .cloud-sidebar {
    left: -260px;
    width: 240px !important;
    box-shadow: var(--shadow-lg);
    transition: left .25s var(--ease);
  }
  .cloud-sidebar.mobile-open { left: 0; }
  body.has-sidebar.mobile-sidebar-open .cs-mobile-overlay { display: block; }
  .ch-mobile-toggle { display: flex; }
  .cs-collapse-btn { display: none; }
  .ch-divider { display: none; }
}

@media (max-width: 480px) {
  .ch-balance { display: none; }
}
</style>

<script>
function toggleSidebar() {
  const sidebar = document.getElementById('cloudSidebar');
  if (!sidebar) return;
  sidebar.classList.toggle('collapsed');
  const collapsed = sidebar.classList.contains('collapsed');
  document.body.classList.toggle('sidebar-collapsed', collapsed);
  localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0');
}

function openMobileSidebar() {
  document.body.classList.add('mobile-sidebar-open');
  document.getElementById('cloudSidebar')?.classList.add('mobile-open');
}

function closeMobileSidebar() {
  document.body.classList.remove('mobile-sidebar-open');
  document.getElementById('cloudSidebar')?.classList.remove('mobile-open');
}

function toggleChDropdown(id) {
  const target = document.getElementById(id);
  document.querySelectorAll('.ch-dropdown.open').forEach(d => {
    if (d !== target) d.classList.remove('open');
  });
  target?.classList.toggle('open');
}

/* ============ NOTIFICATIONS ============ */
let notifsLoaded = false;

function toggleNotifications() {
  toggleChDropdown('chNotif');
  const wrap = document.getElementById('chNotif');
  if (wrap?.classList.contains('open') && !notifsLoaded) {
    loadNotifications();
  }
}

async function loadNotifications() {
  const body = document.getElementById('chNotifBody');
  if (!body) return;

  try {
    const res = await fetch('/dashboard/notifications/dropdown', {
      headers: { 'Accept': 'application/json' }
    });
    const data = await res.json();
    notifsLoaded = true;

    // Badge yangilash
    updateNotifBadge(data.unread_count);

    // Mark all tugma
    document.getElementById('chMarkAllBtn').style.display =
      data.unread_count > 0 ? 'inline-flex' : 'none';

    if (!data.notifications.length) {
      body.innerHTML = `
        <div class="ch-notif-empty">
          <span class="material-icons-round">notifications_none</span>
          Hech qanday xabar yo'q
        </div>`;
      return;
    }

    body.innerHTML = data.notifications.map(n => {
      const url = n.action_url || `/dashboard/notifications`;
      return `
        <a href="${url}" class="ch-notif-item ${n.color} ${!n.is_read ? 'unread' : ''}"
           onclick="markNotifRead(${n.id}); event.stopPropagation();">
          <div class="ch-notif-item-icon">
            <span class="material-icons-round">${n.icon}</span>
          </div>
          <div class="ch-notif-item-content">
            <div class="ch-notif-item-title">${escapeHtml(n.title)}</div>
            <div class="ch-notif-item-msg">${escapeHtml(stripTags(n.message))}</div>
            <span class="ch-notif-item-time">${n.time}</span>
          </div>
        </a>
      `;
    }).join('');
  } catch (e) {
    body.innerHTML = `<div class="ch-notif-empty">Xato yuz berdi</div>`;
  }
}

function updateNotifBadge(count) {
  const badge = document.getElementById('chNotifBadge');
  if (!badge) return;
  if (count > 0) {
    badge.textContent = count > 99 ? '99+' : count;
    badge.style.display = 'flex';
  } else {
    badge.style.display = 'none';
  }
}

async function markNotifRead(id) {
  try {
    await fetch(`/dashboard/notifications/${id}/read`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        'Accept': 'application/json',
      }
    });
  } catch (e) {}
}

async function markAllNotifsRead() {
  try {
    await fetch(`/dashboard/notifications/mark-all-read`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        'Accept': 'application/json',
      }
    });
    // Reload dropdown
    notifsLoaded = false;
    loadNotifications();
  } catch (e) {}
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[c]);
}

function stripTags(str) {
  if (!str) return '';
  return str.replace(/<[^>]*>/g, '');
}

// Avtomatik unread soni — har 60 soniyada
async function checkUnreadCount() {
  try {
    const res = await fetch('/dashboard/notifications/dropdown', {
      headers: { 'Accept': 'application/json' }
    });
    const data = await res.json();
    updateNotifBadge(data.unread_count);
  } catch (e) {}
}

// Birinchi yuklash + polling
document.addEventListener('DOMContentLoaded', () => {
  checkUnreadCount();
  setInterval(checkUnreadCount, 60000); // har minutda
});

(function() {
  if (localStorage.getItem('sidebar-collapsed') === '1') {
    document.getElementById('cloudSidebar')?.classList.add('collapsed');
    document.body.classList.add('sidebar-collapsed');
  }
})();

document.addEventListener('click', (e) => {
  document.querySelectorAll('.ch-dropdown.open').forEach(d => {
    if (!d.contains(e.target)) d.classList.remove('open');
  });
});
</script>