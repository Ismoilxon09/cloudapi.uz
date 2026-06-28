@extends('layouts.app')

@push('styles')
<style>
.legal-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 40px 24px 80px;
}

.legal-header {
  margin-bottom: 32px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--border);
}

.legal-title {
  font-size: 36px;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: var(--text-strong);
  margin-bottom: 8px;
}

.legal-subtitle {
  font-size: 14px;
  color: var(--text-muted);
}

.legal-updated {
  font-size: 12px;
  color: var(--text-subtle);
  margin-top: 8px;
  font-family: 'JetBrains Mono', monospace;
}

.legal-content {
  font-size: 15px;
  line-height: 1.7;
  color: var(--text);
}

.legal-content h2 {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-strong);
  letter-spacing: -0.02em;
  margin-top: 36px;
  margin-bottom: 12px;
}

.legal-content h2:first-child { margin-top: 0; }

.legal-content h3 {
  font-size: 17px;
  font-weight: 700;
  color: var(--text-strong);
  margin-top: 24px;
  margin-bottom: 8px;
}

.legal-content p {
  margin-bottom: 14px;
}

.legal-content ul, .legal-content ol {
  margin-bottom: 14px;
  padding-left: 24px;
}

.legal-content li {
  margin-bottom: 6px;
}

.legal-content strong {
  color: var(--text-strong);
  font-weight: 600;
}

.legal-content a {
  color: var(--accent);
  text-decoration: underline;
  text-decoration-color: rgba(37,99,235,.3);
}

.legal-content a:hover { text-decoration-color: var(--accent); }

.legal-content code {
  font-family: 'JetBrains Mono', monospace;
  font-size: 13px;
  background: var(--bg-subtle);
  padding: 2px 6px;
  border-radius: 4px;
  color: var(--text-strong);
}

.legal-callout {
  background: var(--bg-subtle);
  border-left: 3px solid var(--accent);
  padding: 14px 18px;
  border-radius: 6px;
  margin: 20px 0;
  font-size: 14px;
}

.legal-callout strong { color: var(--text-strong); }

.legal-callout.warning {
  border-left-color: var(--warning);
  background: rgba(245,158,11,.06);
}

.legal-callout.success {
  border-left-color: var(--success);
  background: rgba(16,185,129,.06);
}

.legal-callout.danger {
  border-left-color: var(--danger);
  background: rgba(239,68,68,.06);
}

.legal-toc {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 18px 22px;
  margin-bottom: 32px;
}

.legal-toc-title {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 10px;
}

.legal-toc ol {
  margin: 0;
  padding-left: 20px;
  list-style: decimal;
  font-size: 13px;
  line-height: 1.9;
}

.legal-toc a {
  color: var(--text);
  text-decoration: none;
}

.legal-toc a:hover { color: var(--accent); }

.legal-contact {
  margin-top: 40px;
  padding: 20px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  text-align: center;
}

.legal-contact h3 { margin-top: 0; }
.legal-contact a {
  color: var(--accent);
  font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="legal-page">
  <header class="legal-header">
    <h1 class="legal-title">@yield('legal_title')</h1>
    <div class="legal-subtitle">@yield('legal_subtitle')</div>
    <div class="legal-updated">Oxirgi yangilanish: 24-iyun, 2026</div>
  </header>

  <div class="legal-content">
    @yield('legal_content')
  </div>

  <div class="legal-contact">
    <h3 style="font-size:16px;margin-bottom:8px">Savollar bormi?</h3>
    <p style="margin:0;color:var(--text-muted);font-size:13px">
      Bog'lanish uchun: <a href="https://t.me/coder_nurmatov" target="_blank">@coder_nurmatov</a>
      yoki <a href="mailto:support@cloudapi.uz">support@cloudapi.uz</a>
    </p>
  </div>
</div>
@endsection