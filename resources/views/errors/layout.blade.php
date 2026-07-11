<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('code') — CloudAPI</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('brand/favicon.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #ffffff; --text: #0a0a0a; --muted: #737373; --subtle: #a3a3a3;
  --border: #e5e5e5; --border-strong: #d4d4d4; --primary: #0a0a0a; --on-primary: #ffffff;
}
@media (prefers-color-scheme: dark) {
  :root {
    --bg: #0a0a0a; --text: #ffffff; --muted: #a3a3a3; --subtle: #737373;
    --border: #262626; --border-strong: #404040; --primary: #ffffff; --on-primary: #0a0a0a;
  }
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  background: var(--bg); color: var(--text);
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  padding: 24px; -webkit-font-smoothing: antialiased;
}
.err { text-align: center; max-width: 460px; width: 100%; }
.err-mark { width: 52px; height: 44px; margin: 0 auto 32px; color: var(--text); opacity: 0.9; }
.err-code {
  font-size: clamp(72px, 16vw, 104px); font-weight: 800; line-height: 0.95;
  letter-spacing: -0.05em; margin-bottom: 16px;
}
.err-title { font-size: 22px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 10px; }
.err-msg { font-size: 15px; color: var(--muted); line-height: 1.65; margin: 0 auto 32px; max-width: 380px; }
.err-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.err-btn {
  display: inline-flex; align-items: center; gap: 8px; padding: 12px 22px;
  border-radius: 11px; font-weight: 600; font-size: 14px; text-decoration: none;
  border: 1px solid var(--primary); transition: opacity .15s, background .15s, color .15s;
}
.err-btn-primary { background: var(--primary); color: var(--on-primary); }
.err-btn-primary:hover { opacity: 0.9; }
.err-btn-ghost { background: transparent; color: var(--text); border-color: var(--border-strong); }
.err-btn-ghost:hover { background: var(--border); }
.err-foot { margin-top: 40px; font-size: 12.5px; color: var(--subtle); }
.err-foot a { color: var(--muted); text-decoration: none; }
.err-foot a:hover { color: var(--text); }
</style>
</head>
<body>
<div class="err">
  <div class="err-mark">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" fill="currentColor" width="52" height="44">
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
  <div class="err-code">@yield('code')</div>
  <div class="err-title">@yield('title')</div>
  <div class="err-msg">@yield('message')</div>
  <div class="err-actions">
    <a href="{{ url('/') }}" class="err-btn err-btn-primary">Bosh sahifa</a>
    <a href="{{ url('/dashboard') }}" class="err-btn err-btn-ghost">Panelga o'tish</a>
  </div>
  <div class="err-foot">CloudAPI — 363+ AI model bitta joyda · <a href="{{ url('/dashboard/tickets/create') }}">Yordam</a></div>
</div>
</body>
</html>
