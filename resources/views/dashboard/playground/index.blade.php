@extends('layouts.app')

@section('title', __('playground.title') . ' — CloudAPI')

@push('styles')
<style>
.pg-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 24px;
  height: calc(100vh - var(--header-height));
  display: flex;
  flex-direction: column;
}

.pg-header { margin-bottom: 20px; }

.pg-title {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.pg-subtitle {
  font-size: 13px;
  color: var(--text-muted);
}

.pg-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 16px;
  flex: 1;
  min-height: 0;
}

/* Settings */
.pg-settings {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
  overflow-y: auto;
}

.pg-settings-title {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 16px;
}

.pg-field { margin-bottom: 16px; }

.pg-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
  font-weight: 500;
  color: var(--text-muted);
  margin-bottom: 6px;
}

.pg-label-value {
  font-family: 'JetBrains Mono', monospace;
  color: var(--text-strong);
  font-weight: 600;
  font-size: 11px;
}

.pg-input, .pg-select, .pg-textarea {
  width: 100%;
  padding: 9px 11px;
  font-size: 13px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 8px;
  outline: none;
  transition: all .15s;
  font-family: inherit;
}

.pg-input:focus, .pg-select:focus, .pg-textarea:focus {
  border-color: var(--accent);
  background: var(--bg-elevated);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}

.pg-textarea { resize: vertical; min-height: 80px; }

.pg-range {
  width: 100%;
  accent-color: var(--primary);
}

.pg-cost-card {
  padding: 14px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 10px;
  margin-bottom: 14px;
}

.pg-cost-label {
  font-size: 11px;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-weight: 600;
  margin-bottom: 4px;
}

.pg-cost-value {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-strong);
  font-family: 'JetBrains Mono', monospace;
}

/* Chat */
.pg-chat {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.pg-messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.pg-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  height: 100%;
  color: var(--text-muted);
}

.pg-empty .material-icons-round {
  font-size: 48px;
  color: var(--text-subtle);
  margin-bottom: 12px;
  opacity: 0.5;
}

.pg-empty h3 {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-strong);
  margin-bottom: 6px;
}

.pg-empty p {
  font-size: 13px;
  max-width: 280px;
}

.pg-msg {
  margin-bottom: 16px;
  display: flex;
  gap: 12px;
  animation: msgIn .3s var(--ease-spring) both;
}

@keyframes msgIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

.pg-msg-avatar {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 11px;
  font-weight: 700;
}

.pg-msg.user .pg-msg-avatar {
  background: var(--primary);
  color: var(--bg-elevated);
}

.pg-msg.assistant .pg-msg-avatar {
  background: var(--gray-deep);
  color: white;
}

.pg-msg-content { flex: 1; min-width: 0; }

.pg-msg-role {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 4px;
}

.pg-msg-text {
  font-size: 14px;
  color: var(--text);
  line-height: 1.6;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.pg-msg-meta {
  font-size: 11px;
  color: var(--text-subtle);
  margin-top: 6px;
  font-family: 'JetBrains Mono', monospace;
}

.pg-thinking {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.pg-thinking-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--text-muted);
  animation: dotPulse 1.4s ease-in-out infinite;
}

.pg-thinking-dot:nth-child(2) { animation-delay: 0.2s; }
.pg-thinking-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes dotPulse {
  0%, 60%, 100% { opacity: 0.3; transform: scale(0.8); }
  30% { opacity: 1; transform: scale(1); }
}

/* Input area */
.pg-input-area {
  border-top: 1px solid var(--border);
  padding: 16px;
  display: flex;
  gap: 10px;
  align-items: flex-end;
}

.pg-input-box {
  flex: 1;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 10px 14px;
  transition: all .15s;
}

.pg-input-box:focus-within {
  border-color: var(--accent);
  background: var(--bg-elevated);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}

.pg-input-textarea {
  width: 100%;
  background: transparent;
  border: none;
  outline: none;
  resize: none;
  font-family: inherit;
  font-size: 14px;
  color: var(--text);
  min-height: 24px;
  max-height: 200px;
  line-height: 1.5;
}

.pg-input-textarea::placeholder { color: var(--text-subtle); }

.pg-send-btn {
  flex-shrink: 0;
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: var(--primary);
  color: var(--bg-elevated);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .15s;
}

.pg-send-btn:hover { background: var(--primary-hover); }
.pg-send-btn:disabled {
  background: var(--bg-subtle);
  color: var(--text-subtle);
  cursor: not-allowed;
}

.pg-send-btn .material-icons-round { font-size: 20px; }

@media (max-width: 900px) {
  .pg-layout { grid-template-columns: 1fr; }
  .pg-page { height: auto; }
  .pg-settings { max-height: 400px; }
}
</style>
@endpush

@section('content')

<div class="pg-page">
  <div class="pg-header">
    <h1 class="pg-title">{{ __('playground.title') }}</h1>
    <p class="pg-subtitle">{{ __('playground.subtitle') }}</p>
  </div>

  <div class="pg-layout">
    <!-- Settings -->
    <div class="pg-settings">
      <div class="pg-settings-title">{{ __('playground.settings') }}</div>

      <div class="pg-field">
        <label class="pg-label">{{ __('playground.model') }}</label>
        <select id="modelSelect" class="pg-select" onchange="updateCostEstimate()">
          @foreach($models as $m)
            <option value="{{ $m->model_id }}"
                    data-cost-in="{{ $m->getFinalPriceInput() }}"
                    data-cost-out="{{ $m->getFinalPriceOutput() }}"
                    data-usd="{{ $m->usd_to_uzs ?: 12700 }}"
                    data-free="{{ $m->is_free ? '1' : '0' }}"
                    {{ request('model') === $m->model_id ? 'selected' : '' }}>
              {{ $m->display_name }}{{ $m->is_free ? ' (Bepul)' : '' }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="pg-field">
        <label class="pg-label">
          {{ __('playground.temperature') }}
          <span class="pg-label-value" id="tempValue">0.7</span>
        </label>
        <input type="range" id="temperature" class="pg-range" min="0" max="2" step="0.1" value="0.7"
               oninput="document.getElementById('tempValue').textContent=this.value">
      </div>

      <div class="pg-field">
        <label class="pg-label">{{ __('playground.max_tokens') }}</label>
        <input type="number" id="maxTokens" class="pg-input" value="1000" min="1" max="8000" onchange="updateCostEstimate()">
      </div>

      <div class="pg-field">
        <label class="pg-label">
          {{ __('playground.system_prompt') }}
          <span style="color:var(--text-subtle);font-weight:400">({{ __('playground.optional') }})</span>
        </label>
        <textarea id="systemPrompt" class="pg-textarea" rows="3"
                  placeholder="{{ __('playground.system_placeholder') }}"></textarea>
      </div>

      <div class="pg-cost-card">
        <div class="pg-cost-label">{{ __('playground.est_cost') }}</div>
        <div class="pg-cost-value" id="estCost">— {{ __('common.currency') }}</div>
      </div>

      <button class="btn btn-secondary w-full" onclick="clearChat()">
        <span class="material-icons-round">refresh</span>
        {{ __('playground.clear') }}
      </button>
    </div>

    <!-- Chat -->
    <div class="pg-chat">
      <div class="pg-messages" id="chatMessages">
        <div class="pg-empty" id="chatEmpty">
          <span class="material-icons-round">forum</span>
          <h3>{{ __('playground.empty_chat') }}</h3>
          <p>{{ __('playground.empty_chat_desc') }}</p>
        </div>
      </div>

      <div class="pg-input-area">
        <div class="pg-input-box">
          <textarea class="pg-input-textarea" id="userInput"
                    placeholder="{{ __('playground.placeholder') }}"
                    onkeydown="handleEnter(event)"
                    oninput="autoResize(this)"></textarea>
        </div>
        <button class="pg-send-btn" id="sendBtn" onclick="sendMessage()">
          <span class="material-icons-round">arrow_upward</span>
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
const messages = [];
let isLoading = false;

function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 200) + 'px';
}

function handleEnter(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendMessage();
  }
}

function updateCostEstimate() {
  const select = document.getElementById('modelSelect');
  const opt = select.selectedOptions[0];
  const costIn = parseFloat(opt.dataset.costIn) || 0;
  const costOut = parseFloat(opt.dataset.costOut) || 0;
  const usd = parseFloat(opt.dataset.usd) || 12700;
  const isFree = opt.dataset.free === '1' || (costIn === 0 && costOut === 0);
  const maxTokens = parseInt(document.getElementById('maxTokens').value) || 1000;

  const el = document.getElementById('estCost');

  if (isFree) {
    el.innerHTML = '<span style="color:var(--success);font-weight:700">Bepul</span>';
    return;
  }

  // Estimate: 200 tokens input + maxTokens output
  const costUsd = (200 * costIn / 1000000) + (maxTokens * costOut / 1000000);
  const costUzs = costUsd * usd;

  if (costUzs < 0.01) {
    el.textContent = '< 0.01 {{ __("common.currency") }}';
  } else if (costUzs < 1) {
    el.textContent = `~ ${costUzs.toFixed(4)} {{ __("common.currency") }}`;
  } else {
    el.textContent = `~ ${costUzs.toFixed(2)} {{ __("common.currency") }}`;
  }
}

function addMessage(role, text, meta = '') {
  document.getElementById('chatEmpty')?.remove();
  const container = document.getElementById('chatMessages');
  const div = document.createElement('div');
  div.className = `pg-msg ${role}`;
  div.innerHTML = `
    <div class="pg-msg-avatar">${role === 'user' ? 'U' : 'AI'}</div>
    <div class="pg-msg-content">
      <div class="pg-msg-role">${role === 'user' ? '{{ app()->getLocale() === "uz" ? "Siz" : (app()->getLocale() === "ru" ? "Вы" : "You") }}' : 'Assistant'}</div>
      <div class="pg-msg-text">${escapeHtml(text)}</div>
      ${meta ? `<div class="pg-msg-meta">${meta}</div>` : ''}
    </div>
  `;
  container.appendChild(div);
  container.scrollTop = container.scrollHeight;
  return div;
}

function addThinking() {
  document.getElementById('chatEmpty')?.remove();
  const container = document.getElementById('chatMessages');
  const div = document.createElement('div');
  div.className = 'pg-msg assistant';
  div.id = 'thinkingMsg';
  div.innerHTML = `
    <div class="pg-msg-avatar">AI</div>
    <div class="pg-msg-content">
      <div class="pg-msg-role">{{ __('playground.thinking') }}</div>
      <div class="pg-msg-text">
        <span class="pg-thinking">
          <span class="pg-thinking-dot"></span>
          <span class="pg-thinking-dot"></span>
          <span class="pg-thinking-dot"></span>
        </span>
      </div>
    </div>
  `;
  container.appendChild(div);
  container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

async function sendMessage() {
  if (isLoading) return;
  const input = document.getElementById('userInput');
  const text = input.value.trim();
  if (!text) return;

  isLoading = true;
  document.getElementById('sendBtn').disabled = true;

  messages.push({ role: 'user', content: text });
  addMessage('user', text);
  input.value = '';
  input.style.height = 'auto';

  addThinking();

  try {
    const model = document.getElementById('modelSelect').value;
    const temperature = parseFloat(document.getElementById('temperature').value);
    const maxTokens = parseInt(document.getElementById('maxTokens').value) || 1000;
    const systemPrompt = document.getElementById('systemPrompt').value.trim();

    const fullMessages = [...messages];
    if (systemPrompt) {
      fullMessages.unshift({ role: 'system', content: systemPrompt });
    }

    const res = await fetch('{{ route("playground.run") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
      },
      body: JSON.stringify({ model, messages: fullMessages, temperature, max_tokens: maxTokens })
    });

    const data = await res.json();
    document.getElementById('thinkingMsg')?.remove();

    if (data.error) {
      addMessage('assistant', '{{ __("playground.error") }}: ' + data.error);
    } else {
      const reply = data.choices?.[0]?.message?.content || '';
      messages.push({ role: 'assistant', content: reply });
      const tokensIn = data.usage?.prompt_tokens || 0;
      const tokensOut = data.usage?.completion_tokens || 0;
      const cost = data.cost_uzs || 0;

      // Bepul model bo'lsa "Bepul" yozamiz, aks holda narx
      let costText;
      if (cost <= 0) {
        costText = 'Bepul';
      } else if (cost < 0.01) {
        costText = '< 0.01 {{ __("common.currency") }}';
      } else if (cost < 1) {
        costText = `${cost.toFixed(4)} {{ __("common.currency") }}`;
      } else {
        costText = `${cost.toFixed(2)} {{ __("common.currency") }}`;
      }

      addMessage('assistant', reply, `${tokensIn} → ${tokensOut} tokens · ${costText}`);
    }
  } catch (e) {
    document.getElementById('thinkingMsg')?.remove();
    addMessage('assistant', '{{ __("playground.error") }}: ' + e.message);
  }

  isLoading = false;
  document.getElementById('sendBtn').disabled = false;
  input.focus();
}

function clearChat() {
  messages.length = 0;
  document.getElementById('chatMessages').innerHTML = `
    <div class="pg-empty" id="chatEmpty">
      <span class="material-icons-round">forum</span>
      <h3>{{ __('playground.empty_chat') }}</h3>
      <p>{{ __('playground.empty_chat_desc') }}</p>
    </div>
  `;
}

updateCostEstimate();
</script>
@endpush