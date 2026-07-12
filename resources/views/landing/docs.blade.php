@extends('layouts.app')

@section('title', __('docs.title') . ' — CloudAPI')

@push('styles')
<style>
.docs-page {
  display: grid;
  grid-template-columns: 260px 1fr 220px;
  gap: 32px;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
  align-items: start;
  position: relative;
  z-index: 2;
}

/* When user is logged in, hide docs sidebar (main app sidebar handles navigation) */
body.has-sidebar .docs-page {
  grid-template-columns: 1fr 220px;
  max-width: 1100px;
}

body.has-sidebar .docs-sidebar {
  display: none;
}

/* Docs sidebar */
.docs-sidebar {
  position: sticky;
  top: 80px;
  max-height: calc(100vh - 100px);
  overflow-y: auto;
  padding: 24px 0;
}

.docs-sidebar::-webkit-scrollbar { width: 4px; }
.docs-sidebar::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 99px; }

.docs-search {
  position: relative;
  margin-bottom: 24px;
}

.docs-search-input {
  width: 100%;
  padding: 9px 12px 9px 36px;
  font-size: 13px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 8px;
  outline: none;
}

.docs-search-input:focus {
  border-color: var(--accent);
  background: var(--bg-elevated);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}

.docs-search .material-icons-round {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 16px;
  color: var(--text-subtle);
}

.docs-nav-section {
  margin-bottom: 24px;
}

.docs-nav-title {
  font-size: 10px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 6px 10px;
  margin-bottom: 4px;
}

.docs-nav-link {
  display: block;
  padding: 6px 10px;
  font-size: 13px;
  color: var(--text-muted);
  border-radius: 6px;
  text-decoration: none;
  transition: all .15s;
  border-left: 2px solid transparent;
}

.docs-nav-link:hover { color: var(--text-strong); background: var(--bg-subtle); }
.docs-nav-link.active {
  color: var(--text-strong);
  font-weight: 600;
  border-left-color: var(--primary);
  background: var(--bg-subtle);
}

/* Main content */
.docs-content {
  padding: 24px 0 80px;
  min-width: 0;
  max-width: 760px;
}

.docs-header {
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid var(--border);
}

.docs-breadcrumb {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 12px;
}

.docs-breadcrumb a { color: var(--text-muted); text-decoration: none; }
.docs-breadcrumb a:hover { color: var(--text-strong); }
.docs-breadcrumb .material-icons-round { font-size: 14px; color: var(--text-subtle); }

.docs-title {
  font-size: 36px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 8px;
}

.docs-subtitle {
  font-size: 16px;
  color: var(--text-muted);
  line-height: 1.6;
}

.docs-section {
  margin-bottom: 48px;
  scroll-margin-top: 80px;
}

.docs-section h2 {
  font-size: 24px;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 16px;
  margin-top: 32px;
}

.docs-section h3 {
  font-size: 17px;
  font-weight: 600;
  color: var(--text-strong);
  margin-bottom: 12px;
  margin-top: 24px;
}

.docs-section p {
  font-size: 15px;
  color: var(--text);
  line-height: 1.7;
  margin-bottom: 16px;
}

.docs-section ul, .docs-section ol {
  margin-bottom: 16px;
  padding-left: 24px;
}

.docs-section li {
  font-size: 15px;
  color: var(--text);
  line-height: 1.7;
  margin-bottom: 6px;
}

/* Feature grid */
.docs-features {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  margin: 24px 0;
}

.docs-feature {
  padding: 18px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
}

.docs-feature-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--gray-deep);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
}

.docs-feature-icon .material-icons-round { font-size: 18px; }

.docs-feature h4 {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.docs-feature p {
  font-size: 13px;
  color: var(--text-muted);
  margin: 0;
}

/* Code block */
.docs-code {
  background: #0A0A0A;
  border: 1px solid #1F2937;
  border-radius: 10px;
  overflow: hidden;
  margin: 16px 0;
  position: relative;
}

.docs-code-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 14px;
  background: #111111;
  border-bottom: 1px solid #1F2937;
  font-size: 11px;
  font-weight: 600;
  color: #9CA3AF;
  text-transform: lowercase;
}

.docs-code-copy {
  background: transparent;
  border: 1px solid #1F2937;
  color: #9CA3AF;
  padding: 3px 8px;
  border-radius: 5px;
  font-size: 11px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.docs-code-copy:hover { color: white; border-color: #374151; }
.docs-code-copy .material-icons-round { font-size: 12px; }

.docs-code pre {
  margin: 0;
  padding: 18px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 13px;
  line-height: 1.7;
  color: #D1D5DB;
  overflow-x: auto;
  white-space: pre;
}

.docs-code pre .kw { color: #93C5FD; }
.docs-code pre .str { color: #86EFAC; }
.docs-code pre .com { color: #6B7280; font-style: italic; }
.docs-code pre .fn { color: #F0ABFC; }
.docs-code pre .var { color: #FCD34D; }

/* Tables */
.docs-table-wrap {
  margin: 20px 0;
  border: 1px solid var(--border);
  border-radius: 10px;
  overflow: hidden;
}

.docs-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.docs-table th {
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 10px 14px;
  background: var(--bg-subtle);
  border-bottom: 1px solid var(--border);
}

.docs-table td {
  padding: 12px 14px;
  border-bottom: 1px solid var(--border);
  font-size: 13px;
  color: var(--text);
  vertical-align: top;
}

.docs-table tr:last-child td { border-bottom: none; }

.docs-table code {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  background: var(--bg-subtle);
  padding: 1px 6px;
  border-radius: 4px;
  color: var(--text-strong);
}

/* Callout */
.callout {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
  border-radius: 10px;
  margin: 20px 0;
  border: 1px solid;
}

.callout-icon {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.callout-icon .material-icons-round { font-size: 16px; }

.callout-content { flex: 1; }

.callout-title {
  font-weight: 700;
  font-size: 13px;
  margin-bottom: 4px;
}

.callout-text {
  font-size: 13px;
  line-height: 1.6;
  margin: 0;
}

.callout.info {
  background: rgba(37, 99, 235, .06);
  border-color: rgba(37, 99, 235, .25);
}
.callout.info .callout-icon { background: rgba(37, 99, 235, .12); color: var(--accent); }
.callout.info .callout-title { color: var(--accent); }

.callout.warning {
  background: rgba(245, 158, 11, .06);
  border-color: rgba(245, 158, 11, .25);
}
.callout.warning .callout-icon { background: rgba(245, 158, 11, .12); color: var(--warning); }
.callout.warning .callout-title { color: var(--warning); }

.callout.success {
  background: rgba(16, 185, 129, .06);
  border-color: rgba(16, 185, 129, .25);
}
.callout.success .callout-icon { background: rgba(16, 185, 129, .12); color: var(--success); }
.callout.success .callout-title { color: var(--success); }

/* FAQ */
.faq-item {
  border-bottom: 1px solid var(--border);
  padding: 16px 0;
}

.faq-item:last-child { border-bottom: none; }

.faq-question {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 15px;
  font-weight: 600;
  color: var(--text-strong);
  cursor: pointer;
  list-style: none;
  padding: 4px 0;
}

.faq-question::-webkit-details-marker { display: none; }

.faq-question .material-icons-round {
  font-size: 18px;
  color: var(--text-muted);
  transition: transform .2s;
}

.faq-item[open] .faq-question .material-icons-round { transform: rotate(180deg); }

.faq-answer {
  margin-top: 10px;
  font-size: 14px;
  color: var(--text-muted);
  line-height: 1.7;
}

/* TOC (right) */
.docs-toc {
  position: sticky;
  top: 80px;
  max-height: calc(100vh - 100px);
  overflow-y: auto;
  padding: 24px 0;
}

.docs-toc-title {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 12px;
}

.docs-toc a {
  display: block;
  padding: 4px 0 4px 12px;
  font-size: 12px;
  color: var(--text-muted);
  text-decoration: none;
  border-left: 2px solid var(--border);
  transition: all .15s;
}

.docs-toc a:hover {
  color: var(--text-strong);
  border-left-color: var(--text-muted);
}

.docs-toc a.active {
  color: var(--text-strong);
  border-left-color: var(--primary);
  font-weight: 600;
}

/* Inline code */
.docs-content code:not(pre code) {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.9em;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  padding: 1px 6px;
  border-radius: 4px;
  color: var(--text-strong);
}

.docs-content a:not(.docs-nav-link):not(.docs-toc a) {
  color: var(--accent);
  text-decoration: none;
}

.docs-content a:not(.docs-nav-link):not(.docs-toc a):hover { text-decoration: underline; }

/* Next steps cards */
.next-steps {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
  margin: 24px 0;
}

.next-step {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  text-decoration: none;
  color: inherit;
  transition: all .15s;
}

.next-step:hover {
  border-color: var(--text-muted);
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.next-step-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: var(--gray-deep);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.next-step-text {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-strong);
}

.next-step-arrow {
  margin-left: auto;
  color: var(--text-subtle);
}

@media (max-width: 1100px) {
  .docs-page { grid-template-columns: 240px 1fr; }
  .docs-toc { display: none; }
  /* Login qilingan userда docs-sidebar yashirin bo'lgani uchun kontent to'liq kenglikda */
  body.has-sidebar .docs-page { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .docs-page { grid-template-columns: 1fr; padding: 0 16px; }
  .docs-sidebar { display: none; }
  .docs-title { font-size: 28px; }
}
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
      <a href="{{ route('models.index') }}" class="nav-link">{{ __('landing.nav.models') }}</a>
      <a href="{{ route('pricing') }}" class="nav-link">{{ __('landing.nav.pricing') }}</a>
      <a href="{{ route('docs') }}" class="nav-link active">{{ __('landing.nav.docs') }}</a>
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
  <!-- LEFT SIDEBAR -->
  <aside class="docs-sidebar">
    <div class="docs-search">
      <span class="material-icons-round">search</span>
      <input type="text" class="docs-search-input" placeholder="{{ __('docs.search_placeholder') }}" id="docsSearch">
    </div>

    <div class="docs-nav-section">
      <div class="docs-nav-title">{{ __('docs.sidebar.getting_started') }}</div>
      <a href="#intro" class="docs-nav-link active">{{ __('docs.sidebar.introduction') }}</a>
      <a href="#quickstart" class="docs-nav-link">{{ __('docs.sidebar.quickstart') }}</a>
      <a href="#auth" class="docs-nav-link">{{ __('docs.sidebar.authentication') }}</a>
    </div>

    <div class="docs-nav-section">
      <div class="docs-nav-title">{{ __('docs.sidebar.api_reference') }}</div>
      <a href="#chat-completions" class="docs-nav-link">{{ __('docs.sidebar.chat_completions') }}</a>
      <a href="#models-endpoint" class="docs-nav-link">{{ __('docs.sidebar.models_endpoint') }}</a>
      <a href="#streaming" class="docs-nav-link">{{ __('docs.sidebar.streaming') }}</a>
    </div>

    <div class="docs-nav-section">
      <div class="docs-nav-title">{{ __('docs.sidebar.guides') }}</div>
      <a href="#pricing-doc" class="docs-nav-link">{{ __('docs.sidebar.pricing_billing') }}</a>
      <a href="#rate-limits" class="docs-nav-link">{{ __('docs.sidebar.rate_limits') }}</a>
      <a href="#errors-doc" class="docs-nav-link">{{ __('docs.sidebar.errors') }}</a>
    </div>

    <div class="docs-nav-section">
      <div class="docs-nav-title">{{ __('docs.sidebar.support') }}</div>
      <a href="#faq" class="docs-nav-link">{{ __('docs.sidebar.faq') }}</a>
      <a href="#contact" class="docs-nav-link">{{ __('docs.sidebar.contact') }}</a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="docs-content">
    <div class="docs-header">
      <div class="docs-breadcrumb">
        <a href="{{ route('home') }}">CloudAPI</a>
        <span class="material-icons-round">chevron_right</span>
        <span>{{ __('docs.title') }}</span>
      </div>
      <h1 class="docs-title">{{ __('docs.title') }}</h1>
      <p class="docs-subtitle">{{ __('docs.subtitle') }}</p>
    </div>

    <!-- INTRODUCTION -->
    <section class="docs-section" id="intro">
      <h2>{{ __('docs.intro.title') }}</h2>
      <p>{{ __('docs.intro.description') }}</p>

      <h3>{{ __('docs.intro.why_title') }}</h3>
      <div class="docs-features">
        <div class="docs-feature">
          <div class="docs-feature-icon"><span class="material-icons-round">bolt</span></div>
          <h4>{{ __('docs.intro.feature_1_title') }}</h4>
          <p>{{ __('docs.intro.feature_1_desc') }}</p>
        </div>
        <div class="docs-feature">
          <div class="docs-feature-icon"><span class="material-icons-round">payments</span></div>
          <h4>{{ __('docs.intro.feature_2_title') }}</h4>
          <p>{{ __('docs.intro.feature_2_desc') }}</p>
        </div>
        <div class="docs-feature">
          <div class="docs-feature-icon"><span class="material-icons-round">memory</span></div>
          <h4>{{ __('docs.intro.feature_3_title') }}</h4>
          <p>{{ __('docs.intro.feature_3_desc') }}</p>
        </div>
        <div class="docs-feature">
          <div class="docs-feature-icon"><span class="material-icons-round">trending_up</span></div>
          <h4>{{ __('docs.intro.feature_4_title') }}</h4>
          <p>{{ __('docs.intro.feature_4_desc') }}</p>
        </div>
      </div>

      <h3>{{ __('docs.intro.next_steps') }}</h3>
      <div class="next-steps">
        <a href="{{ route('register') }}" class="next-step">
          <div class="next-step-icon"><span class="material-icons-round">person_add</span></div>
          <div class="next-step-text">{{ __('docs.intro.create_account') }}</div>
          <span class="material-icons-round next-step-arrow">arrow_forward</span>
        </a>
        <a href="#quickstart" class="next-step">
          <div class="next-step-icon"><span class="material-icons-round">key</span></div>
          <div class="next-step-text">{{ __('docs.intro.get_api_key') }}</div>
          <span class="material-icons-round next-step-arrow">arrow_forward</span>
        </a>
        <a href="#chat-completions" class="next-step">
          <div class="next-step-icon"><span class="material-icons-round">send</span></div>
          <div class="next-step-text">{{ __('docs.intro.first_request') }}</div>
          <span class="material-icons-round next-step-arrow">arrow_forward</span>
        </a>
      </div>
    </section>

    <!-- QUICKSTART -->
    <section class="docs-section" id="quickstart">
      <h2>{{ __('docs.quickstart.title') }}</h2>
      <p>{{ __('docs.quickstart.subtitle') }}</p>

      <h3>{{ __('docs.quickstart.step1') }}</h3>
      <p>{{ __('docs.quickstart.step1_desc') }}</p>

      <h3>{{ __('docs.quickstart.step2') }}</h3>
      <p>{{ __('docs.quickstart.step2_desc') }}</p>

      <div class="docs-code">
        <div class="docs-code-header">
          <span>bash</span>
          <button class="docs-code-copy" onclick="copyDocsCode(this)">
            <span class="material-icons-round">content_copy</span>{{ __('docs.copy_code') }}
          </button>
        </div>
<pre><span class="com"># Python</span>
pip install openai

<span class="com"># Node.js</span>
npm install openai</pre>
      </div>

      <h3>{{ __('docs.quickstart.step3') }}</h3>
      <p>{{ __('docs.quickstart.step3_desc') }}</p>

      <div class="docs-code">
        <div class="docs-code-header">
          <span>python</span>
          <button class="docs-code-copy" onclick="copyDocsCode(this)">
            <span class="material-icons-round">content_copy</span>{{ __('docs.copy_code') }}
          </button>
        </div>
<pre><span class="kw">from</span> openai <span class="kw">import</span> OpenAI

client = <span class="fn">OpenAI</span>(
    api_key=<span class="str">"cap-..."</span>,
    base_url=<span class="str">"https://api.cloudapi.uz/v1"</span>
)

response = client.chat.completions.<span class="fn">create</span>(
    model=<span class="str">"anthropic/claude-3.5-sonnet"</span>,
    messages=[{<span class="str">"role"</span>: <span class="str">"user"</span>, <span class="str">"content"</span>: <span class="str">"Salom!"</span>}]
)

<span class="fn">print</span>(response.choices[<span class="var">0</span>].message.content)</pre>
      </div>

      <div class="callout success">
        <div class="callout-icon"><span class="material-icons-round">check_circle</span></div>
        <div class="callout-content">
          <div class="callout-title">{{ __('docs.quickstart.try_now') }}</div>
          <p class="callout-text">
            <a href="{{ route('playground.index') }}">{{ __('docs.quickstart.try_now') }} →</a>
          </p>
        </div>
      </div>
    </section>

    <!-- AUTHENTICATION -->
    <section class="docs-section" id="auth">
      <h2>{{ __('docs.auth.title') }}</h2>
      <p>{{ __('docs.auth.description') }}</p>

      <h3>{{ __('docs.auth.header_format') }}</h3>
      <div class="docs-code">
        <div class="docs-code-header">
          <span>http</span>
          <button class="docs-code-copy" onclick="copyDocsCode(this)">
            <span class="material-icons-round">content_copy</span>{{ __('docs.copy_code') }}
          </button>
        </div>
<pre>Authorization: Bearer cap-abc123xyz...</pre>
      </div>

      <div class="callout warning">
        <div class="callout-icon"><span class="material-icons-round">warning</span></div>
        <div class="callout-content">
          <div class="callout-title">{{ __('docs.auth.security_title') }}</div>
          <p class="callout-text">{{ __('docs.auth.security_desc') }}</p>
        </div>
      </div>
    </section>

    <!-- CHAT COMPLETIONS -->
    <section class="docs-section" id="chat-completions">
      <h2>{{ __('docs.chat.title') }}</h2>
      <p>{{ __('docs.chat.description') }}</p>

      <h3>{{ __('docs.chat.endpoint') }}</h3>
      <div class="docs-code">
        <div class="docs-code-header">
          <span>endpoint</span>
        </div>
<pre>POST https://api.cloudapi.uz/v1/chat/completions</pre>
      </div>

      <h3>{{ __('docs.chat.params') }}</h3>
      <div class="docs-table-wrap">
        <table class="docs-table">
          <thead>
            <tr>
              <th>{{ __('common.name') }}</th>
              <th>Type</th>
              <th>{{ __('common.description') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr><td><code>model</code></td><td>string</td><td>{{ __('docs.chat.param_model') }}</td></tr>
            <tr><td><code>messages</code></td><td>array</td><td>{{ __('docs.chat.param_messages') }}</td></tr>
            <tr><td><code>temperature</code></td><td>number</td><td>{{ __('docs.chat.param_temperature') }}</td></tr>
            <tr><td><code>max_tokens</code></td><td>integer</td><td>{{ __('docs.chat.param_max_tokens') }}</td></tr>
            <tr><td><code>stream</code></td><td>boolean</td><td>{{ __('docs.chat.param_stream') }}</td></tr>
          </tbody>
        </table>
      </div>

      <h3>{{ __('docs.chat.example_request') }}</h3>
      <div class="docs-code">
        <div class="docs-code-header">
          <span>curl</span>
          <button class="docs-code-copy" onclick="copyDocsCode(this)">
            <span class="material-icons-round">content_copy</span>{{ __('docs.copy_code') }}
          </button>
        </div>
<pre>curl https://api.cloudapi.uz/v1/chat/completions \
  -H <span class="str">"Authorization: Bearer cap-..."</span> \
  -H <span class="str">"Content-Type: application/json"</span> \
  -d <span class="str">'{
    "model": "anthropic/claude-3.5-sonnet",
    "messages": [{"role": "user", "content": "Salom!"}],
    "temperature": 0.7,
    "max_tokens": 1000
  }'</span></pre>
      </div>
    </section>

    <!-- PRICING -->
    <section class="docs-section" id="pricing-doc">
      <h2>{{ __('docs.pricing_doc.title') }}</h2>
      <p>{{ __('docs.pricing_doc.description') }}</p>

      <h3>{{ __('docs.pricing_doc.how_works_title') }}</h3>
      <ul>
        <li>{{ __('docs.pricing_doc.how_works_1') }}</li>
        <li>{{ __('docs.pricing_doc.how_works_2') }}</li>
        <li>{{ __('docs.pricing_doc.how_works_3') }}</li>
        <li>{{ __('docs.pricing_doc.how_works_4') }}</li>
      </ul>

      <div class="callout info">
        <div class="callout-icon"><span class="material-icons-round">free_breakfast</span></div>
        <div class="callout-content">
          <div class="callout-title">{{ __('docs.pricing_doc.free_models') }}</div>
          <p class="callout-text">{{ __('docs.pricing_doc.free_models_desc') }}</p>
        </div>
      </div>
    </section>

    <!-- RATE LIMITS -->
    <section class="docs-section" id="rate-limits">
      <h2>{{ __('docs.rate_limits.title') }}</h2>
      <p>{{ __('docs.rate_limits.description') }}</p>

      <div class="callout info">
        <div class="callout-icon"><span class="material-icons-round">speed</span></div>
        <div class="callout-content">
          <div class="callout-title">{{ __('docs.rate_limits.default_limit') }}</div>
        </div>
      </div>

      <h3>{{ __('docs.rate_limits.headers_title') }}</h3>
      <div class="docs-table-wrap">
        <table class="docs-table">
          <tbody>
            <tr><td><code>X-RateLimit-Limit</code></td><td>{{ __('docs.rate_limits.header_1') }}</td></tr>
            <tr><td><code>X-RateLimit-Remaining</code></td><td>{{ __('docs.rate_limits.header_2') }}</td></tr>
            <tr><td><code>X-RateLimit-Reset</code></td><td>{{ __('docs.rate_limits.header_3') }}</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ERRORS -->
    <section class="docs-section" id="errors-doc">
      <h2>{{ __('docs.errors_doc.title') }}</h2>
      <p>{{ __('docs.errors_doc.description') }}</p>

      <h3>{{ __('docs.errors_doc.codes') }}</h3>
      <div class="docs-table-wrap">
        <table class="docs-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>{{ __('common.description') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr><td><code>400</code></td><td>{{ __('docs.errors_doc.err_400') }}</td></tr>
            <tr><td><code>401</code></td><td>{{ __('docs.errors_doc.err_401') }}</td></tr>
            <tr><td><code>402</code></td><td>{{ __('docs.errors_doc.err_402') }}</td></tr>
            <tr><td><code>429</code></td><td>{{ __('docs.errors_doc.err_429') }}</td></tr>
            <tr><td><code>500</code></td><td>{{ __('docs.errors_doc.err_500') }}</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- FAQ -->
    <section class="docs-section" id="faq">
      <h2>{{ __('docs.faq.title') }}</h2>

      @foreach(['q1' => 'a1', 'q2' => 'a2', 'q3' => 'a3', 'q4' => 'a4', 'q5' => 'a5', 'q6' => 'a6', 'q7' => 'a7', 'q8' => 'a8'] as $q => $a)
      <details class="faq-item">
        <summary class="faq-question">
          <span>{{ __("docs.faq.{$q}") }}</span>
          <span class="material-icons-round">expand_more</span>
        </summary>
        <div class="faq-answer">{{ __("docs.faq.{$a}") }}</div>
      </details>
      @endforeach
    </section>

    <!-- CONTACT -->
    <section class="docs-section" id="contact">
      <h2>{{ __('docs.contact.title') }}</h2>
      <p>{{ __('docs.contact.description') }}</p>

      <div class="docs-features">
        <a href="mailto:support@cloudapi.uz" class="docs-feature" style="text-decoration:none;color:inherit">
          <div class="docs-feature-icon"><span class="material-icons-round">email</span></div>
          <h4>{{ __('docs.contact.email') }}</h4>
          <p>support@cloudapi.uz</p>
        </a>
        <a href="https://t.me/coder_nurmatov" target="_blank" class="docs-feature" style="text-decoration:none;color:inherit">
          <div class="docs-feature-icon"><span class="material-icons-round">send</span></div>
          <h4>{{ __('docs.contact.telegram') }}</h4>
          <p>@coder_nurmatov</p>
        </a>
      </div>

      <p style="color:var(--text-muted);font-size:13px">{{ __('docs.contact.response_time') }}</p>
    </section>
  </main>

  <!-- RIGHT TOC -->
  <aside class="docs-toc">
    <div class="docs-toc-title">{{ __('docs.on_this_page') }}</div>
    <a href="#intro" class="active">{{ __('docs.intro.title') }}</a>
    <a href="#quickstart">{{ __('docs.quickstart.title') }}</a>
    <a href="#auth">{{ __('docs.auth.title') }}</a>
    <a href="#chat-completions">{{ __('docs.chat.title') }}</a>
    <a href="#pricing-doc">{{ __('docs.pricing_doc.title') }}</a>
    <a href="#rate-limits">{{ __('docs.rate_limits.title') }}</a>
    <a href="#errors-doc">{{ __('docs.errors_doc.title') }}</a>
    <a href="#faq">{{ __('docs.faq.title') }}</a>
    <a href="#contact">{{ __('docs.contact.title') }}</a>
  </aside>
</div>

@endsection

@push('scripts')
<script>
function copyDocsCode(btn) {
  const code = btn.closest('.docs-code').querySelector('pre').textContent;
  navigator.clipboard.writeText(code);
  const orig = btn.innerHTML;
  btn.innerHTML = '<span class="material-icons-round">check</span>{{ __("docs.copied") }}';
  setTimeout(() => btn.innerHTML = orig, 1500);
}

// Active section tracking on scroll
const sections = document.querySelectorAll('.docs-section');
const navLinks = document.querySelectorAll('.docs-nav-link, .docs-toc a');

function updateActive() {
  let current = '';
  sections.forEach(s => {
    const rect = s.getBoundingClientRect();
    if (rect.top <= 100 && rect.bottom >= 100) current = s.id;
  });

  navLinks.forEach(l => {
    l.classList.toggle('active', l.getAttribute('href') === '#' + current);
  });
}

window.addEventListener('scroll', updateActive, { passive: true });

// Sidebar search filter
document.getElementById('docsSearch')?.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.docs-nav-link').forEach(link => {
    const text = link.textContent.toLowerCase();
    link.style.display = !q || text.includes(q) ? 'block' : 'none';
  });
});
</script>
@endpush