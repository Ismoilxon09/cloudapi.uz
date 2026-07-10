<!DOCTYPE html>
<html lang="uz" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Chat') — CloudAPI</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('brand/favicon.svg') }}">
<link rel="apple-touch-icon" href="{{ asset('brand/favicon.svg') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<style>
:root {
  --bg: #ffffff;
  --bg-elevated: #ffffff;
  --bg-subtle: #f7f7f7;
  --bg-hover: #f0f0f0;

  --text-strong: #0a0a0a;
  --text: #262626;
  --text-muted: #737373;
  --text-subtle: #a3a3a3;

  --border: #e5e5e5;
  --border-strong: #d4d4d4;

  --primary: #0a0a0a;
  --primary-hover: #262626;

  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --info: #3b82f6;

  --shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
  --shadow: 0 4px 12px rgba(0,0,0,0.06);
  --shadow-lg: 0 20px 40px rgba(0,0,0,0.08);

  --sidebar-w: 280px;
}

[data-theme="dark"] {
  --bg: #0a0a0a;
  --bg-elevated: #141414;
  --bg-subtle: #1a1a1a;
  --bg-hover: #242424;

  --text-strong: #ffffff;
  --text: #e5e5e5;
  --text-muted: #a3a3a3;
  --text-subtle: #737373;

  --border: #262626;
  --border-strong: #404040;

  --primary: #ffffff;
  --primary-hover: #e5e5e5;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  background: var(--bg);
  color: var(--text);
  font-size: 14px;
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
  overflow: hidden;
}

/* ============================================================
   LAYOUT
============================================================ */
.chat-app {
  display: flex;
  height: 100vh;
  width: 100vw;
}

/* SIDEBAR */
.chat-sidebar {
  width: var(--sidebar-w);
  background: var(--bg-subtle);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  transition: width 0.2s ease;
}

.chat-sidebar-header {
  padding: 16px;
  border-bottom: 1px solid var(--border);
}

.chat-back-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 8px;
  color: var(--text-muted);
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.15s;
  margin-bottom: 12px;
}

.chat-back-btn:hover {
  background: var(--bg-hover);
  color: var(--text-strong);
}

.chat-back-btn .material-icons-round { font-size: 18px; }

.chat-new-btn {
  width: 100%;
  padding: 12px 14px;
  background: var(--primary);
  color: var(--bg-elevated);
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: opacity 0.15s;
  font-family: inherit;
}

.chat-new-btn:hover { opacity: 0.9; }

.chat-new-btn .material-icons-round { font-size: 18px; }

/* Sessions list */
.chat-sessions {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
}

.chat-sessions::-webkit-scrollbar { width: 6px; }
.chat-sessions::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 3px; }

.chat-session-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.1s;
  color: var(--text);
  text-decoration: none;
  font-size: 13.5px;
  position: relative;
  margin-bottom: 2px;
}

.chat-session-item:hover {
  background: var(--bg-hover);
}

.chat-session-item.active {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  font-weight: 600;
  color: var(--text-strong);
}

.chat-session-title {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chat-session-actions {
  display: none;
  gap: 2px;
}

.chat-session-item:hover .chat-session-actions {
  display: flex;
}

.chat-session-actions button {
  width: 24px;
  height: 24px;
  border: none;
  background: transparent;
  border-radius: 4px;
  cursor: pointer;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.1s;
}

.chat-session-actions button:hover {
  background: var(--bg-hover);
  color: var(--text-strong);
}

.chat-session-actions .material-icons-round { font-size: 15px; }

.chat-empty {
  text-align: center;
  padding: 32px 16px;
  color: var(--text-subtle);
  font-size: 12.5px;
}

/* Sidebar footer */
.chat-sidebar-footer {
  padding: 12px 16px;
  border-top: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
}

.chat-balance {
  flex: 1;
  color: var(--text-muted);
}

.chat-balance b {
  color: var(--text-strong);
  font-weight: 700;
}

.chat-sidebar-footer button {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  border-radius: 6px;
  padding: 4px 8px;
  cursor: pointer;
  font-size: 11.5px;
  font-weight: 600;
  transition: all 0.15s;
}

.chat-sidebar-footer button:hover {
  color: var(--text-strong);
  border-color: var(--border-strong);
}

/* ============================================================
   MAIN CHAT AREA
============================================================ */
.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--bg);
  min-width: 0;
}

.chat-topbar {
  height: 56px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  padding: 0 20px;
  gap: 12px;
  background: var(--bg-elevated);
}

.chat-topbar-title {
  font-weight: 700;
  font-size: 14px;
  color: var(--text-strong);
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chat-topbar-brand {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 800;
  color: var(--text-strong);
  text-decoration: none;
  font-size: 14px;
}

.chat-topbar-brand-mark {
  width: 24px;
  height: 24px;
  color: var(--text-strong);
}

.model-picker {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  color: var(--text-strong);
  font-weight: 500;
  cursor: pointer;
  min-width: 200px;
  font-family: inherit;
  transition: all 0.15s;
}

.model-picker:hover {
  border-color: var(--border-strong);
}

.model-picker .material-icons-round {
  font-size: 16px;
  color: var(--text-muted);
}

.chat-topbar-actions {
  display: flex;
  align-items: center;
  gap: 6px;
}

.chat-icon-btn {
  width: 36px;
  height: 36px;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}

.chat-icon-btn:hover {
  background: var(--bg-hover);
  color: var(--text-strong);
}

.chat-icon-btn .material-icons-round { font-size: 18px; }

/* Messages area */
.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 24px 0;
}

.chat-messages::-webkit-scrollbar { width: 8px; }
.chat-messages::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 4px; }

.chat-messages-inner {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 24px;
}

.chat-welcome {
  text-align: center;
  padding: 80px 20px;
  color: var(--text-muted);
}

.chat-welcome-icon {
  font-size: 56px !important;
  margin-bottom: 16px;
  opacity: 0.4;
}

.chat-welcome h2 {
  font-size: 24px;
  font-weight: 800;
  color: var(--text-strong);
  margin-bottom: 8px;
  letter-spacing: -0.02em;
}

.chat-welcome p {
  font-size: 14px;
  max-width: 400px;
  margin: 0 auto;
}

.chat-suggestions {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  max-width: 500px;
  margin: 32px auto 0;
}

.chat-suggestion {
  padding: 14px 16px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 10px;
  font-size: 13px;
  color: var(--text);
  cursor: pointer;
  text-align: left;
  transition: all 0.15s;
  font-family: inherit;
  line-height: 1.4;
}

.chat-suggestion:hover {
  background: var(--bg-hover);
  border-color: var(--border-strong);
}

/* Message bubble */
.msg {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
  animation: msgIn 0.2s ease;
}

@keyframes msgIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}

.msg-avatar {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 13px;
}

.msg-user .msg-avatar {
  background: var(--primary);
  color: var(--bg-elevated);
}

.msg-assistant .msg-avatar {
  background: linear-gradient(135deg, #10B981, #059669);
  color: white;
}

.msg-body {
  flex: 1;
  min-width: 0;
}

.msg-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}

.msg-name {
  font-weight: 700;
  color: var(--text-strong);
  font-size: 13.5px;
}

.msg-model {
  font-size: 11px;
  color: var(--text-subtle);
  padding: 1px 6px;
  background: var(--bg-subtle);
  border-radius: 4px;
  font-family: 'JetBrains Mono', monospace;
}

.msg-content {
  color: var(--text);
  font-size: 14.5px;
  line-height: 1.65;
  word-wrap: break-word;
}

.msg-content p { margin-bottom: 10px; }
.msg-content p:last-child { margin-bottom: 0; }

.msg-content code {
  background: var(--bg-subtle);
  padding: 2px 5px;
  border-radius: 4px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 13px;
  color: var(--text-strong);
}

.msg-content pre {
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  padding: 14px 16px;
  border-radius: 8px;
  overflow-x: auto;
  margin: 10px 0;
  font-family: 'JetBrains Mono', monospace;
  font-size: 13px;
  line-height: 1.5;
}

.msg-content pre code {
  background: transparent;
  padding: 0;
}

.msg-content strong { color: var(--text-strong); }

.msg-actions {
  display: flex;
  gap: 4px;
  margin-top: 8px;
  opacity: 0;
  transition: opacity 0.15s;
}

.msg:hover .msg-actions { opacity: 1; }

.msg-action {
  padding: 4px 8px;
  background: transparent;
  border: 1px solid transparent;
  border-radius: 6px;
  color: var(--text-muted);
  font-size: 11.5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 3px;
  transition: all 0.15s;
  font-family: inherit;
}

.msg-action:hover {
  background: var(--bg-hover);
  color: var(--text-strong);
}

.msg-action .material-icons-round { font-size: 13px; }

.msg-meta {
  display: flex;
  gap: 8px;
  font-size: 11px;
  color: var(--text-subtle);
  margin-top: 6px;
}

.msg-error .msg-content {
  color: var(--danger);
  padding: 10px 14px;
  background: rgba(239, 68, 68, 0.08);
  border-radius: 8px;
  border-left: 3px solid var(--danger);
}

/* Typing indicator */
.typing-indicator {
  display: inline-flex;
  gap: 3px;
  padding: 6px 0;
}

.typing-indicator span {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--text-muted);
  animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
  0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
  30% { opacity: 1; transform: translateY(-3px); }
}

/* Input area */
.chat-input-area {
  border-top: 1px solid var(--border);
  padding: 16px 24px 20px;
  background: var(--bg-elevated);
}

.chat-input-inner {
  max-width: 800px;
  margin: 0 auto;
}

.chat-input-box {
  background: var(--bg-elevated);
  border: 1.5px solid var(--border);
  border-radius: 16px;
  padding: 12px 14px;
  transition: border-color 0.15s;
  box-shadow: var(--shadow-sm);
}

.chat-input-box:focus-within {
  border-color: var(--border-strong);
}

.chat-input-box textarea {
  width: 100%;
  min-height: 24px;
  max-height: 200px;
  border: none;
  outline: none;
  resize: none;
  font-family: inherit;
  font-size: 14.5px;
  color: var(--text-strong);
  background: transparent;
  line-height: 1.5;
}

.chat-input-box textarea::placeholder {
  color: var(--text-subtle);
}

.chat-input-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 8px;
}

.chat-input-tools {
  display: flex;
  gap: 4px;
}

.chat-input-tool {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}

.chat-input-tool:hover {
  background: var(--bg-hover);
  color: var(--text-strong);
}

.chat-input-tool .material-icons-round { font-size: 18px; }

.chat-send-btn {
  padding: 8px 16px;
  background: var(--primary);
  color: var(--bg-elevated);
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13.5px;
  transition: opacity 0.15s;
  font-family: inherit;
}

.chat-send-btn:hover { opacity: 0.9; }

.chat-send-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.chat-send-btn .material-icons-round { font-size: 16px; }

.chat-hint {
  text-align: center;
  color: var(--text-subtle);
  font-size: 11px;
  margin-top: 8px;
}

/* Model dropdown */
.model-dropdown {
  display: none;
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 4px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  box-shadow: var(--shadow-lg);
  max-height: 400px;
  overflow-y: auto;
  width: 320px;
  z-index: 100;
}

.model-dropdown.open { display: block; }

.model-dropdown-search {
  padding: 10px 12px;
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  background: var(--bg-elevated);
}

.model-dropdown-search input {
  width: 100%;
  padding: 8px 10px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 12.5px;
  color: var(--text-strong);
  outline: none;
  font-family: inherit;
}

.model-dropdown-search input:focus {
  border-color: var(--border-strong);
}

.model-option {
  padding: 10px 14px;
  cursor: pointer;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.1s;
}

.model-option:last-child { border-bottom: none; }
.model-option:hover { background: var(--bg-hover); }

.model-option.selected {
  background: var(--bg-subtle);
}

.model-option-info { flex: 1; min-width: 0; }

.model-option-name {
  font-weight: 600;
  font-size: 13px;
  color: var(--text-strong);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.model-option-meta {
  font-size: 11px;
  color: var(--text-muted);
  display: flex;
  gap: 6px;
  margin-top: 2px;
}

.model-badge-free {
  padding: 1px 6px;
  background: #10B98120;
  color: #10B981;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
}

.model-badge-vision {
  padding: 1px 6px;
  background: #3B82F620;
  color: #3B82F6;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
}

/* Mobile */
@media (max-width: 768px) {
  .chat-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 100;
    transform: translateX(-100%);
    transition: transform 0.2s ease;
    box-shadow: var(--shadow-lg);
  }
  .chat-sidebar.open { transform: translateX(0); }
  .chat-messages-inner { padding: 0 16px; }
  .chat-input-area { padding: 12px 16px 16px; }
  .model-picker { min-width: auto; }
  .chat-topbar { padding: 0 12px; }
}
</style>

@stack('styles')

@php
  $chatCssPath = public_path('assets/chat.css');
  $chatJsPath = public_path('assets/chat.js');
  $chatCssV = file_exists($chatCssPath) ? filemtime($chatCssPath) : '1';
  $chatJsV = file_exists($chatJsPath) ? filemtime($chatJsPath) : '1';
@endphp
<link rel="stylesheet" href="{{ asset('assets/chat.css') }}?v={{ $chatCssV }}">
<script defer src="{{ asset('assets/chat.js') }}?v={{ $chatJsV }}"></script>
</head>
<body>

@yield('content')

<script>
// Theme
(function() {
  const saved = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
}

// Format UZS
function fmtUzs(n) {
  return new Intl.NumberFormat('uz-UZ').format(Math.round(n));
}

// Auto-grow textarea
function autoGrow(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 200) + 'px';
}
</script>

@stack('scripts')
</body>
</html>