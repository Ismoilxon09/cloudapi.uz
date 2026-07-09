/* ============================================================
   CloudAPI Chat — front-end app (streaming SSE + markdown +
   real provider logos, pro model picker, rename, pin).
   Bundled with esbuild. Reads window.CHAT_BOOT / window.CHAT_MODELS.
============================================================ */
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import hljs from 'highlight.js/lib/common';
import { vendorMark, vendorName, vendorKey } from './providerLogos';
import './../css/chat.css';

const BOOT = window.CHAT_BOOT || {};
const MODELS = window.CHAT_MODELS || [];
const MODEL_BY_ID = Object.fromEntries(MODELS.map((m) => [m.id, m]));
const CSRF =
  BOOT.csrf ||
  document.querySelector('meta[name="csrf-token"]')?.content ||
  '';
// Navigation base (admin panel uses /admin/chat; actions still hit routes.base)
const VIEW_BASE = BOOT.routes?.viewBase || BOOT.routes?.base || '/dashboard/chat';

const state = {
  sessionId: BOOT.sessionId ?? null,
  model: BOOT.currentModel ?? null,
  modelName: BOOT.currentModelName ?? 'Model tanlang',
  filter: 'all',
  sending: false,
  abort: null,
  stickToBottom: true,
  attachments: [],
  systemPrompt: BOOT.sessionSystemPrompt ?? '',
  temperature: BOOT.sessionTemperature ?? 0.7,
};

marked.setOptions({ gfm: true, breaks: true });

// ============================================================
//  Markdown
// ============================================================
function renderMarkdown(el, raw, { highlight = true } = {}) {
  el.innerHTML = DOMPurify.sanitize(marked.parse(raw || ''));
  el.classList.add('prose');
  el.querySelectorAll('a[href]').forEach((a) => {
    a.setAttribute('target', '_blank');
    a.setAttribute('rel', 'noopener noreferrer');
  });
  if (highlight) enhanceCodeBlocks(el);
}

function enhanceCodeBlocks(container) {
  container.querySelectorAll('pre').forEach((pre) => {
    if (pre.dataset.enhanced) return;
    const code = pre.querySelector('code');
    if (!code) return;
    let lang = '';
    const m = (code.className || '').match(/language-([\w-]+)/);
    if (m) lang = m[1];
    try { hljs.highlightElement(code); } catch (_) { /* unknown lang */ }

    const wrap = document.createElement('div');
    wrap.className = 'code-block';
    const head = document.createElement('div');
    head.className = 'code-head';
    head.innerHTML =
      `<span class="code-lang">${escapeHtml(lang || 'code')}</span>` +
      `<button class="code-copy" type="button" data-action="copy-code">` +
      `<span class="material-icons-round">content_copy</span>Copy</button>`;
    pre.parentNode.insertBefore(wrap, pre);
    wrap.appendChild(head);
    wrap.appendChild(pre);
    pre.dataset.enhanced = '1';
  });
}

// ============================================================
//  Messages
// ============================================================
function assistantAvatar(modelId) {
  return modelId ? vendorMark(modelId, 22)
    : '<span class="material-icons-round" style="font-size:18px">auto_awesome</span>';
}

function createMessageEl(role, { name, modelId = null, raw = '', images = [] } = {}) {
  const div = document.createElement('div');
  div.className = 'msg msg-' + role;

  const avatar = role === 'user'
    ? escapeHtml((name || '?').charAt(0).toUpperCase())
    : assistantAvatar(modelId);

  const displayName = role === 'user'
    ? (name || 'Siz')
    : (modelId ? vendorName(modelId) : 'Assistant');
  const modelBadge = modelId ? `<span class="msg-model">${escapeHtml(modelId)}</span>` : '';

  div.innerHTML = `
    <div class="msg-avatar">${avatar}</div>
    <div class="msg-body">
      <div class="msg-header">
        <span class="msg-name">${escapeHtml(displayName)}</span>
        ${modelBadge}
      </div>
      <div class="msg-content"></div>
    </div>`;

  const contentEl = div.querySelector('.msg-content');
  contentEl._md = raw;
  if (role === 'user') {
    contentEl.classList.add('msg-text');
    contentEl.textContent = raw;
    if (!raw) contentEl.style.display = 'none';
  }

  if (images && images.length) {
    const wrap = document.createElement('div');
    wrap.className = 'msg-images';
    images.forEach((src) => {
      const a = document.createElement('a');
      a.href = src; a.target = '_blank'; a.rel = 'noopener';
      const im = document.createElement('img');
      im.src = src; im.className = 'msg-image'; im.alt = '';
      a.appendChild(im);
      wrap.appendChild(a);
    });
    div.querySelector('.msg-header').insertAdjacentElement('afterend', wrap);
  }
  return div;
}

function addActions(msgEl) {
  const body = msgEl.querySelector('.msg-body');
  if (!body || body.querySelector('.msg-actions')) return;
  const actions = document.createElement('div');
  actions.className = 'msg-actions';
  actions.innerHTML =
    `<button class="msg-action" data-action="copy-msg" type="button">` +
    `<span class="material-icons-round">content_copy</span>Nusxa</button>`;
  body.appendChild(actions);
}

function addMeta(msgEl, tokensIn, tokensOut, cost) {
  const body = msgEl.querySelector('.msg-body');
  if (!body) return;
  const parts = [];
  if (tokensIn || tokensOut) parts.push(`↓ ${tokensIn} · ↑ ${tokensOut} tokens`);
  if (cost > 0) parts.push(`${Number(cost).toFixed(2)} UZS`);
  if (!parts.length) return;
  const meta = document.createElement('div');
  meta.className = 'msg-meta';
  meta.innerHTML = `<span>${parts.join(' · ')}</span>`;
  body.appendChild(meta);
}

const typingIndicator = () =>
  '<div class="typing-indicator"><span></span><span></span><span></span></div>';

function streamingPlaceholder() {
  const cat = MODEL_BY_ID[state.model]?.category;
  if (cat === 'image') {
    return '<div class="gen-skeleton"><div class="gen-shimmer"></div>' +
      '<div class="gen-label"><span class="material-icons-round">auto_awesome</span><span class="gen-status">Rasm yaratilmoqda…</span></div></div>';
  }
  if (cat === 'audio') {
    return '<div class="gen-skeleton gen-audio"><div class="gen-shimmer"></div>' +
      '<div class="gen-label"><span class="material-icons-round">music_note</span><span class="gen-status">Audio yaratilmoqda…</span></div></div>';
  }
  if (cat === 'video') {
    return '<div class="gen-skeleton gen-video"><div class="gen-shimmer"></div>' +
      '<div class="gen-label"><span class="material-icons-round">movie</span><span class="gen-status">Video yaratilmoqda… (1-3 daqiqa)</span></div></div>';
  }
  return typingIndicator();
}

// ============================================================
//  Send (streaming SSE)
// ============================================================
async function sendMessage() {
  if (state.sending) return;
  const textarea = document.getElementById('chatInput');
  const content = textarea.value.trim();
  if (!content && !state.attachments.length) return;
  if (!state.model) { toast('Iltimos, model tanlang'); return; }

  const imgs = state.attachments.slice();
  const inner = document.getElementById('messagesInner');
  inner.querySelector('.chat-welcome')?.remove();

  const userMsg = createMessageEl('user', {
    name: BOOT.userName || 'Siz',
    raw: content,
    images: imgs.map((a) => a.data),
  });
  inner.appendChild(userMsg);

  state.attachments = [];
  renderPreviews();
  textarea.value = '';
  textarea.style.height = 'auto';

  const assistantMsg = mkAssistantStreaming(inner);

  await runStream(BOOT.routes.stream, {
    session_id: state.sessionId,
    model_id: state.model,
    content,
    images: imgs.map((a) => ({ data: a.data, name: a.name, mime: a.mime })),
  }, assistantMsg, {
    onUserMessage: (event) => {
      if (event.message?.id) {
        userMsg.dataset.messageId = event.message.id;
        addUserActions(userMsg);
      }
      if (event.session_id && !state.sessionId) {
        state.sessionId = event.session_id;
        history.replaceState({}, '', `${VIEW_BASE}/${event.session_id}`);
        addSidebarSession(event.session_title, event.session_id);
        setTopbarTitle(event.session_title, event.session_id);
      }
    },
  });
  textarea.focus();
}

// Shared SSE consumer for send / regenerate / edit
async function runStream(url, body, assistantMsg, opts = {}) {
  const contentDiv = assistantMsg.querySelector('.msg-content');
  let full = '';
  let started = false;
  let renderTimer = null;
  const flushRender = () => {
    renderTimer = null;
    renderMarkdown(contentDiv, full, { highlight: false });
    autoScroll();
  };

  state.sending = true;
  document.body.classList.add('is-streaming');
  state.abort = new AbortController();
  try {
    const res = await fetch(url, {
      method: 'POST',
      signal: state.abort.signal,
      headers: {
        'X-CSRF-TOKEN': CSRF,
        'Content-Type': 'application/json',
        Accept: 'text/event-stream',
      },
      body: JSON.stringify(body),
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const reader = res.body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';
    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      buffer += decoder.decode(value, { stream: true });
      const lines = buffer.split('\n');
      buffer = lines.pop() || '';
      for (const line of lines) {
        if (!line.startsWith('data: ')) continue;
        const data = line.slice(6).trim();
        if (data === '[DONE]') continue;
        let event;
        try { event = JSON.parse(data); } catch { continue; }

        if (event.type === 'user_message') {
          opts.onUserMessage?.(event);
        } else if (event.type === 'delta') {
          if (!started) { contentDiv.innerHTML = ''; started = true; }
          full += event.content;
          if (!renderTimer) renderTimer = setTimeout(flushRender, 60);
        } else if (event.type === 'done') {
          const msg = event.message || {};
          addMeta(assistantMsg, msg.tokens_input, msg.tokens_output, msg.cost_uzs);
          if (event.new_balance !== undefined) updateBalance(event.new_balance);
        } else if (event.type === 'image') {
          if (!started) { contentDiv.innerHTML = ''; started = true; }
          appendAssistantImage(assistantMsg, event.url);
        } else if (event.type === 'audio') {
          if (!started) { contentDiv.innerHTML = ''; started = true; }
          appendAssistantAudio(assistantMsg, event.url);
        } else if (event.type === 'video_status') {
          const s = contentDiv.querySelector('.gen-status');
          if (s) s.textContent = event.status;
        } else if (event.type === 'video') {
          if (!started) { contentDiv.innerHTML = ''; started = true; }
          appendAssistantVideo(assistantMsg, event.url);
        } else if (event.type === 'title') {
          syncTitle(event.session_id, event.title);
        } else if (event.type === 'error') {
          started = true;
          contentDiv.innerHTML = `<span style="color:var(--danger)">${escapeHtml(event.error)}</span>`;
        }
      }
    }
  } catch (e) {
    if (e.name !== 'AbortError') {
      started = true;
      contentDiv.innerHTML = `<span style="color:var(--danger)">Tarmoq xatosi: ${escapeHtml(e.message)}</span>`;
    }
  } finally {
    if (renderTimer) clearTimeout(renderTimer);
    contentDiv._md = full;
    if (full.trim()) {
      renderMarkdown(contentDiv, full, { highlight: true });
      addActions(assistantMsg);
    } else if (!assistantMsg.querySelector('.msg-images, .msg-audio, .msg-video-wrap') && !contentDiv.textContent.trim()) {
      contentDiv.innerHTML = '<span style="color:var(--text-subtle)">(bo\'sh javob)</span>';
    }
    assistantMsg.classList.remove('msg-streaming');
    document.body.classList.remove('is-streaming');
    state.sending = false;
    state.abort = null;
    refreshRegenButton();
    autoScroll();
  }
}

function mkAssistantStreaming(inner) {
  const el = createMessageEl('assistant', { modelId: state.model });
  el.classList.add('msg-streaming');
  el.querySelector('.msg-content').innerHTML = streamingPlaceholder();
  inner.appendChild(el);
  state.stickToBottom = true;
  scrollToBottom();
  return el;
}

// Regenerate the last assistant response
async function regenerate() {
  if (state.sending || !state.sessionId) return;
  const inner = document.getElementById('messagesInner');
  const assistants = inner.querySelectorAll('.msg-assistant');
  assistants[assistants.length - 1]?.remove();
  const assistantMsg = mkAssistantStreaming(inner);
  await runStream(`${BOOT.routes.base}/${state.sessionId}/regenerate`, {
    model_id: state.model,
  }, assistantMsg, {});
}

// Edit a user message inline, then resend
function editUserMessage(userMsgEl) {
  if (!userMsgEl || state.sending) return;
  const body = userMsgEl.querySelector('.msg-body');
  if (!body || body.querySelector('.edit-box')) return;
  const c = userMsgEl.querySelector('.msg-content');
  const old = c?._md ?? c?.textContent ?? '';
  const box = document.createElement('div');
  box.className = 'edit-box';
  box.innerHTML =
    `<textarea class="edit-textarea"></textarea>` +
    `<div class="edit-actions">` +
    `<button class="edit-cancel" data-action="edit-cancel" type="button">Bekor</button>` +
    `<button class="edit-save" data-action="edit-save" type="button">Saqlab yuborish</button>` +
    `</div>`;
  box._userMsg = userMsgEl;
  const ta = box.querySelector('textarea');
  ta.value = old;
  if (c) c.style.display = 'none';
  body.appendChild(box);
  const grow = () => { ta.style.height = 'auto'; ta.style.height = ta.scrollHeight + 'px'; };
  ta.addEventListener('input', grow);
  ta.focus();
  grow();
}

function closeEditBox(box, save) {
  const userMsgEl = box._userMsg;
  const val = box.querySelector('textarea').value.trim();
  box.remove();
  const c = userMsgEl.querySelector('.msg-content');
  if (c && !save) c.style.display = (c._md || c.textContent) ? '' : 'none';
  if (save) submitEdit(userMsgEl, val);
}

async function submitEdit(userMsgEl, newContent) {
  const mid = userMsgEl.dataset.messageId;
  if (!mid || !state.sessionId) { toast("Bu xabarni tahrirlab bo'lmaydi"); return; }
  if (state.sending) return;

  const c = userMsgEl.querySelector('.msg-content');
  if (c) { c.textContent = newContent; c._md = newContent; c.style.display = newContent ? '' : 'none'; }

  let n = userMsgEl.nextElementSibling;
  while (n) { const next = n.nextElementSibling; n.remove(); n = next; }

  const inner = document.getElementById('messagesInner');
  const assistantMsg = mkAssistantStreaming(inner);
  await runStream(`${BOOT.routes.base}/${state.sessionId}/edit`, {
    message_id: Number(mid),
    content: newContent,
    model_id: state.model,
  }, assistantMsg, {});
}

function addUserActions(userMsgEl) {
  if (!userMsgEl?.dataset.messageId) return;
  const body = userMsgEl.querySelector('.msg-body');
  if (!body || body.querySelector('.msg-actions')) return;
  const actions = document.createElement('div');
  actions.className = 'msg-actions';
  actions.innerHTML =
    `<button class="msg-action" data-action="edit-msg" type="button">` +
    `<span class="material-icons-round">edit</span>Tahrirlash</button>`;
  body.appendChild(actions);
}

function refreshRegenButton() {
  const assistants = document.querySelectorAll('.msg-assistant');
  assistants.forEach((el, i) => {
    const existing = el.querySelector('[data-action="regenerate"]');
    const isLast = i === assistants.length - 1 && !el.classList.contains('msg-streaming');
    if (isLast) {
      addActions(el);
      const actions = el.querySelector('.msg-actions');
      if (actions && !existing) {
        const b = document.createElement('button');
        b.className = 'msg-action';
        b.dataset.action = 'regenerate';
        b.type = 'button';
        b.innerHTML = '<span class="material-icons-round">refresh</span>Qayta';
        actions.appendChild(b);
      }
    } else if (existing) {
      existing.remove();
    }
  });
}

// Per-chat settings: system prompt + temperature
function openSettings() {
  if (!state.sessionId) { toast('Sozlama uchun avval xabar yuboring'); return; }
  const panel = document.getElementById('settingsPanel') || buildSettingsPanel();
  panel.classList.toggle('open');
}

function buildSettingsPanel() {
  const panel = document.createElement('div');
  panel.id = 'settingsPanel';
  panel.className = 'settings-panel';
  panel.innerHTML =
    `<div class="settings-head">Chat sozlamalari</div>` +
    `<label class="settings-label">System ko'rsatma (ixtiyoriy)</label>` +
    `<textarea id="settingsPrompt" class="settings-textarea" placeholder="Masalan: Sen tajribali dasturchisan, faqat o'zbek tilida javob ber."></textarea>` +
    `<label class="settings-label">Temperatura: <b id="settingsTempVal"></b></label>` +
    `<input type="range" id="settingsTemp" min="0" max="2" step="0.1" class="settings-range">` +
    `<div class="settings-actions">` +
    `<button class="settings-cancel" data-action="close-settings" type="button">Yopish</button>` +
    `<button class="settings-save" data-action="save-settings" type="button">Saqlash</button>` +
    `</div>`;
  (document.querySelector('.chat-main') || document.body).appendChild(panel);
  panel.querySelector('#settingsPrompt').value = state.systemPrompt || '';
  const temp = panel.querySelector('#settingsTemp');
  const tv = panel.querySelector('#settingsTempVal');
  temp.value = state.temperature ?? 0.7;
  tv.textContent = Number(temp.value).toFixed(1);
  temp.addEventListener('input', () => { tv.textContent = Number(temp.value).toFixed(1); });
  return panel;
}

async function saveSettings() {
  const panel = document.getElementById('settingsPanel');
  if (!panel) return;
  const sp = panel.querySelector('#settingsPrompt').value.trim();
  const tp = parseFloat(panel.querySelector('#settingsTemp').value);
  state.systemPrompt = sp;
  state.temperature = tp;
  try {
    await fetch(`${BOOT.routes.base}/${state.sessionId}/settings`, {
      method: 'PUT',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ system_prompt: sp, temperature: tp }),
    });
    toast('Sozlamalar saqlandi');
  } catch { toast('Saqlashda xato'); }
  panel.classList.remove('open');
}

function stopGeneration() { if (state.abort) state.abort.abort(); }

// ============================================================
//  Model picker
// ============================================================
function fmtPrice(n) {
  if (!n) return '0';
  if (n >= 100) return Math.round(n).toLocaleString('en');
  if (n >= 1) return n.toFixed(1);
  return n.toFixed(2);
}

function filteredModels() {
  const q = (document.getElementById('modelSearch')?.value || '').toLowerCase().trim();
  return MODELS.filter((m) => {
    if (state.filter === 'free' && !m.free) return false;
    if (['vision', 'reasoning', 'code', 'image', 'audio', 'video'].includes(state.filter) && m.category !== state.filter) return false;
    if (q) {
      const hay = `${m.name} ${m.id} ${vendorName(m.id)} ${m.provider}`.toLowerCase();
      if (!hay.includes(q)) return false;
    }
    return true;
  });
}

function optionHtml(m) {
  const badges = [];
  if (m.free) badges.push('<span class="model-badge-free">Free</span>');
  if (m.category === 'vision') badges.push('<span class="model-badge-vision">Vision</span>');
  else if (m.category === 'reasoning') badges.push('<span class="model-badge-reason">Reason</span>');
  else if (m.category === 'code') badges.push('<span class="model-badge-code">Code</span>');

  const ctx = m.ctx ? `${new Intl.NumberFormat('en').format(m.ctx)} tok` : '';
  const price = m.free ? 'Bepul'
    : (m.in || m.out) ? `${fmtPrice(m.in)}/${fmtPrice(m.out)} UZS·1M` : '';
  const meta = [ctx, price].filter(Boolean).join(' · ');

  return `<div class="model-option" data-action="select-model" data-model-id="${escapeAttr(m.id)}" data-model-name="${escapeAttr(m.name)}">
    <span class="model-option-logo">${vendorMark(m.id, 26)}</span>
    <div class="model-option-info">
      <div class="model-option-name">${escapeHtml(m.name)}</div>
      <div class="model-option-meta">${escapeHtml(meta)}</div>
    </div>
    <div class="model-option-badges">${badges.join('')}</div>
  </div>`;
}

function buildModelPicker() {
  const list = document.getElementById('modelList');
  if (!list) return;
  const models = filteredModels();
  if (!models.length) {
    list.innerHTML = '<div class="model-empty">Model topilmadi</div>';
    return;
  }
  const groups = new Map();
  for (const m of models) {
    const k = vendorKey(m.id);
    if (!groups.has(k)) groups.set(k, []);
    groups.get(k).push(m);
  }
  const html = [];
  for (const arr of groups.values()) {
    html.push(`<div class="model-group"><span>${escapeHtml(vendorName(arr[0].id))}</span><small>${arr.length}</small></div>`);
    for (const m of arr) html.push(optionHtml(m));
  }
  list.innerHTML = html.join('');
  markSelected();
}

function markSelected() {
  document.querySelectorAll('.model-option').forEach((o) => o.classList.remove('selected'));
  if (state.model) {
    const el = document.querySelector(`.model-option[data-model-id="${cssEscape(state.model)}"]`);
    el?.classList.add('selected');
  }
}

function setCurrentModelLogo() {
  const el = document.getElementById('currentModelLogo');
  if (el) el.innerHTML = state.model ? vendorMark(state.model, 20) : '';
}

function toggleModelDropdown() {
  const dd = document.getElementById('modelDropdown');
  const opening = !dd.classList.contains('open');
  dd.classList.toggle('open', opening);
  if (opening) {
    buildModelPicker();
    const s = document.getElementById('modelSearch');
    if (s) { s.value = ''; setTimeout(() => s.focus(), 30); }
  }
}

function selectModel(modelId, modelName) {
  state.model = modelId;
  state.modelName = modelName || MODEL_BY_ID[modelId]?.name || modelId;
  document.getElementById('currentModelName').textContent = state.modelName;
  setCurrentModelLogo();
  document.getElementById('modelDropdown').classList.remove('open');
  markSelected();
  updateAttachAvailability();
  updateComposerHint();
  if (!canAttach() && state.attachments.length) {
    state.attachments = [];
    renderPreviews();
  }
}

// ============================================================
//  Attachments (vision image upload)
// ============================================================
function isVisionModel() {
  const m = MODEL_BY_ID[state.model];
  return !!(m && m.category === 'vision');
}

function canAttach() {
  const cat = MODEL_BY_ID[state.model]?.category;
  return cat === 'vision' || cat === 'image';
}

function updateAttachAvailability() {
  const btn = document.getElementById('attachBtn');
  if (!btn) return;
  const ok = canAttach();
  btn.disabled = !ok;
  btn.title = ok ? 'Rasm biriktirish' : 'Rasm biriktirish uchun vision yoki rasm modelini tanlang';
  btn.classList.toggle('active', ok);
}

function updateComposerHint() {
  const ta = document.getElementById('chatInput');
  if (!ta) return;
  const cat = MODEL_BY_ID[state.model]?.category;
  if (cat === 'image') ta.placeholder = "Yaratmoqchi bo'lgan rasmni tasvirlab bering...";
  else if (cat === 'audio') ta.placeholder = "Yaratmoqchi bo'lgan musiqa/audioni tasvirlab bering...";
  else if (cat === 'video') ta.placeholder = "Yaratmoqchi bo'lgan videoni tasvirlab bering...";
  else ta.placeholder = 'Xabar yozing... (Enter yubor, Shift+Enter yangi qator)';
}

function handleFiles(fileList) {
  for (const f of Array.from(fileList || [])) {
    if (!f.type.startsWith('image/')) { toast('Faqat rasm fayllari'); continue; }
    if (f.size > 6 * 1024 * 1024) { toast(`"${f.name}" juda katta (max 6MB)`); continue; }
    if (state.attachments.length >= 4) { toast("Ko'pi bilan 4 ta rasm"); break; }
    const reader = new FileReader();
    reader.onload = () => {
      state.attachments.push({ data: reader.result, name: f.name, mime: f.type, size: f.size });
      renderPreviews();
    };
    reader.readAsDataURL(f);
  }
}

function renderPreviews() {
  const box = document.getElementById('attachPreviews');
  if (!box) return;
  if (!state.attachments.length) { box.innerHTML = ''; box.classList.remove('show'); return; }
  box.classList.add('show');
  box.innerHTML = state.attachments.map((a, i) =>
    `<div class="attach-chip"><img src="${a.data}" alt="">` +
    `<button type="button" class="attach-remove" data-action="remove-attach" data-idx="${i}">` +
    `<span class="material-icons-round">close</span></button></div>`
  ).join('');
}

function appendAssistantImage(msgEl, url) {
  let wrap = msgEl.querySelector('.msg-images');
  if (!wrap) {
    wrap = document.createElement('div');
    wrap.className = 'msg-images';
    msgEl.querySelector('.msg-content')?.insertAdjacentElement('afterend', wrap);
  }
  const a = document.createElement('a');
  a.href = url; a.target = '_blank'; a.rel = 'noopener';
  const im = document.createElement('img');
  im.src = url; im.className = 'msg-image'; im.alt = '';
  a.appendChild(im);
  wrap.appendChild(a);
  autoScroll();
}

function appendAssistantAudio(msgEl, url) {
  const audio = document.createElement('audio');
  audio.controls = true;
  audio.src = url;
  audio.className = 'msg-audio';
  msgEl.querySelector('.msg-content')?.insertAdjacentElement('afterend', audio);
  autoScroll();
}

function appendAssistantVideo(msgEl, url) {
  const wrap = document.createElement('div');
  wrap.className = 'msg-video-wrap';
  const v = document.createElement('video');
  v.controls = true;
  v.src = url;
  v.className = 'msg-video';
  v.setAttribute('playsinline', '');
  wrap.appendChild(v);
  msgEl.querySelector('.msg-content')?.insertAdjacentElement('afterend', wrap);
  autoScroll();
}

// ============================================================
//  Sessions: new / delete / pin / rename
// ============================================================
function newChat() { window.location.href = BOOT.routes.index; }

async function deleteSession(sessionId) {
  if (!confirm("Chatni o'chirmoqchimisiz?")) return;
  try {
    const res = await fetch(`${BOOT.routes.base}/${sessionId}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
    });
    if (res.ok) {
      if (state.sessionId === sessionId) window.location.href = BOOT.routes.index;
      else document.querySelector(`[data-session-id="${sessionId}"]`)?.remove();
    } else toast("O'chirishda xato");
  } catch (e) { toast('Xato: ' + e.message); }
}

async function pinSession(sessionId) {
  try {
    const res = await fetch(`${BOOT.routes.base}/${sessionId}/pin`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
    });
    const data = await res.json();
    if (!data.ok) return;
    const item = document.querySelector(`.chat-session-item[data-session-id="${sessionId}"]`);
    if (!item) return;
    item.classList.toggle('pinned', data.is_pinned);
    const pin = item.querySelector('.chat-session-pin');
    if (pin) pin.style.display = data.is_pinned ? '' : 'none';
    if (data.is_pinned) {
      const list = document.getElementById('sessionsList');
      list.insertBefore(item, list.firstChild);
    }
    toast(data.is_pinned ? 'Mahkamlandi' : 'Mahkamlash olindi');
  } catch (e) { toast('Xato: ' + e.message); }
}

function addSidebarSession(title, sessionId) {
  const list = document.getElementById('sessionsList');
  list.querySelector('.chat-empty')?.remove();
  list.querySelectorAll('.chat-session-item.active').forEach((el) => el.classList.remove('active'));
  const item = document.createElement('a');
  item.href = `${VIEW_BASE}/${sessionId}`;
  item.className = 'chat-session-item active';
  item.dataset.sessionId = sessionId;
  item.innerHTML = `
    <span class="material-icons-round chat-session-pin" style="display:none">push_pin</span>
    <span class="chat-session-title">${escapeHtml(title || 'Yangi chat')}</span>
    <div class="chat-session-actions">
      <button data-action="pin-session" data-session-id="${sessionId}" title="Mahkamlash" type="button"><span class="material-icons-round">push_pin</span></button>
      <button data-action="rename-session" data-session-id="${sessionId}" title="Nomini o'zgartirish" type="button"><span class="material-icons-round">edit</span></button>
      <button data-action="delete-session" data-session-id="${sessionId}" title="O'chirish" type="button"><span class="material-icons-round">delete_outline</span></button>
    </div>`;
  list.insertBefore(item, list.firstChild);
}

function setTopbarTitle(title, sessionId) {
  let box = document.getElementById('chatTitleEdit');
  if (!box) {
    const brand = document.querySelector('.chat-topbar-brand');
    if (!brand) return;
    box = document.createElement('div');
    box.className = 'chat-title-edit';
    box.id = 'chatTitleEdit';
    box.dataset.sessionId = sessionId;
    box.innerHTML = `<span class="chat-title-text" id="chatTitleText"></span>
      <button class="chat-title-btn" data-action="rename-current" title="Nomini o'zgartirish" type="button"><span class="material-icons-round">edit</span></button>
      <button class="chat-title-btn" data-action="open-settings" title="Sozlamalar" type="button"><span class="material-icons-round">tune</span></button>`;
    brand.insertAdjacentElement('afterend', box);
  }
  box.dataset.sessionId = sessionId;
  const t = document.getElementById('chatTitleText');
  if (t) t.textContent = title;
  document.title = `${title} — CloudAPI`;
}

async function saveTitle(sessionId, title) {
  try {
    await fetch(`${BOOT.routes.base}/${sessionId}/title`, {
      method: 'PUT',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ title }),
    });
  } catch (_) { /* keep optimistic UI */ }
}

function syncTitle(sessionId, title) {
  const item = document.querySelector(`.chat-session-item[data-session-id="${sessionId}"] .chat-session-title`);
  if (item) item.textContent = title;
  if (Number(sessionId) === Number(state.sessionId)) {
    const t = document.getElementById('chatTitleText');
    if (t) t.textContent = title;
    document.title = `${title} — CloudAPI`;
  }
}

function editTitleInline(spanEl, sessionId) {
  if (!spanEl) return;
  const old = spanEl.textContent.trim();
  const input = document.createElement('input');
  input.type = 'text';
  input.className = 'title-edit-input';
  input.value = old;
  input.maxLength = 100;
  spanEl.replaceWith(input);
  input.focus();
  input.select();

  let done = false;
  const finish = (save) => {
    if (done) return;
    done = true;
    const val = input.value.trim();
    const span = document.createElement('span');
    span.className = spanEl.className;
    if (spanEl.id) span.id = spanEl.id;
    const final = save && val ? val : old;
    span.textContent = final;
    input.replaceWith(span);
    if (save && val && val !== old) {
      saveTitle(sessionId, val);
      syncTitle(sessionId, val);
    }
  };
  input.addEventListener('keydown', (e) => {
    e.stopPropagation();
    if (e.key === 'Enter') { e.preventDefault(); finish(true); }
    else if (e.key === 'Escape') { e.preventDefault(); finish(false); }
  });
  input.addEventListener('blur', () => finish(true));
  input.addEventListener('click', (e) => e.preventDefault());
}

// ============================================================
//  Sidebar (mobile) / scroll / balance / toast
// ============================================================
function toggleSidebar(force) {
  const sidebar = document.getElementById('chatSidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  const open = force !== undefined ? force : !sidebar.classList.contains('open');
  sidebar.classList.toggle('open', open);
  backdrop?.classList.toggle('show', open);
}

function scrollToBottom() {
  const box = document.getElementById('chatMessages');
  box.scrollTop = box.scrollHeight;
}
function autoScroll() { if (state.stickToBottom) scrollToBottom(); }
function nearBottom(box) { return box.scrollHeight - box.scrollTop - box.clientHeight < 80; }

function updateBalance(value) {
  const el = document.getElementById('balance');
  if (el) el.textContent = new Intl.NumberFormat('uz-UZ').format(Math.round(value));
}

let toastTimer = null;
function toast(text) {
  let el = document.getElementById('chatToast');
  if (!el) {
    el = document.createElement('div');
    el.id = 'chatToast';
    el.className = 'chat-toast';
    document.body.appendChild(el);
  }
  el.textContent = text;
  requestAnimationFrame(() => el.classList.add('show'));
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 2000);
}

async function copyText(text, btn) {
  try {
    await navigator.clipboard.writeText(text);
    if (btn) {
      btn.classList.add('copied');
      const icon = btn.querySelector('.material-icons-round');
      const prev = icon ? icon.textContent : null;
      if (icon) icon.textContent = 'check';
      setTimeout(() => { btn.classList.remove('copied'); if (icon && prev) icon.textContent = prev; }, 1400);
    }
  } catch { toast('Nusxa olishda xato'); }
}

// ============================================================
//  Image lightbox (zoom + download + copy + share)
// ============================================================
function openLightbox(src) {
  if (!src) return;
  const lb = document.getElementById('lightbox') || buildLightbox();
  lb.querySelector('.lightbox-img').src = src;
  lb._src = src;
  lb.classList.add('open');
}

function buildLightbox() {
  const lb = document.createElement('div');
  lb.id = 'lightbox';
  lb.className = 'lightbox';
  lb.innerHTML =
    `<div class="lightbox-toolbar">` +
    `<button data-action="lb-download" title="Yuklab olish" type="button"><span class="material-icons-round">download</span></button>` +
    `<button data-action="lb-copy" title="Nusxa olish" type="button"><span class="material-icons-round">content_copy</span></button>` +
    `<button data-action="lb-share" title="Ulashish" type="button"><span class="material-icons-round">share</span></button>` +
    `<button data-action="lb-close" title="Yopish" type="button"><span class="material-icons-round">close</span></button>` +
    `</div><img class="lightbox-img" src="" alt="">`;
  document.body.appendChild(lb);
  lb.addEventListener('click', (e) => { if (e.target === lb) lb.classList.remove('open'); });
  return lb;
}

function closeLightbox() {
  document.getElementById('lightbox')?.classList.remove('open');
}

async function downloadImage(src) {
  if (!src) return;
  try {
    let href = src;
    if (/^https?:/.test(src)) href = URL.createObjectURL(await (await fetch(src)).blob());
    const a = document.createElement('a');
    a.href = href;
    a.download = `cloudapi-${Date.now()}.png`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    if (href !== src) setTimeout(() => URL.revokeObjectURL(href), 1000);
  } catch { toast('Yuklab olishda xato'); }
}

async function copyImage(src) {
  if (!src) return;
  try {
    const blob = await (await fetch(src)).blob();
    await navigator.clipboard.write([new ClipboardItem({ [blob.type]: blob })]);
    toast('Rasm nusxalandi');
  } catch { toast('Nusxa olishda xato'); }
}

async function shareImage(src) {
  if (!src) return;
  try {
    const blob = await (await fetch(src)).blob();
    const file = new File([blob], 'cloudapi.png', { type: blob.type });
    if (navigator.canShare && navigator.canShare({ files: [file] })) {
      await navigator.share({ files: [file], title: 'CloudAPI' });
    } else {
      toast("Ulashish bu qurilmada mavjud emas");
    }
  } catch (e) { if (e.name !== 'AbortError') toast('Ulashishda xato'); }
}

// ============================================================
//  Utils
// ============================================================
function escapeHtml(text) {
  const d = document.createElement('div');
  d.textContent = text == null ? '' : String(text);
  return d.innerHTML;
}
function escapeAttr(text) {
  return escapeHtml(text).replace(/"/g, '&quot;');
}
function cssEscape(s) {
  return window.CSS && CSS.escape ? CSS.escape(s) : String(s).replace(/["\\]/g, '\\$&');
}

// ============================================================
//  Init
// ============================================================
function init() {
  // stored assistant messages -> markdown + vendor avatar
  document.querySelectorAll('.msg-content[data-md]').forEach((el) => {
    const raw = el.textContent;
    el._md = raw;
    renderMarkdown(el, raw, { highlight: true });
    addActions(el.closest('.msg'));
  });
  document.querySelectorAll('.msg-avatar[data-model-logo]').forEach((el) => {
    const mid = el.getAttribute('data-model-logo');
    if (mid) el.innerHTML = vendorMark(mid, 22);
  });

  // stored user messages -> edit action; last assistant -> regenerate
  document.querySelectorAll('.msg-user[data-message-id]').forEach((el) => addUserActions(el));
  refreshRegenButton();

  // current model label + logo
  const nameEl = document.getElementById('currentModelName');
  if (nameEl && state.modelName) nameEl.textContent = state.modelName;
  setCurrentModelLogo();

  const textarea = document.getElementById('chatInput');
  textarea?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
  });
  textarea?.addEventListener('input', () => {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 200) + 'px';
  });

  document.getElementById('modelSearch')?.addEventListener('input', buildModelPicker);
  document.getElementById('modelFilters')?.addEventListener('click', (e) => {
    const b = e.target.closest('.model-filter');
    if (!b) return;
    document.querySelectorAll('.model-filter').forEach((x) => x.classList.remove('active'));
    b.classList.add('active');
    state.filter = b.dataset.filter;
    buildModelPicker();
  });

  document.getElementById('attachInput')?.addEventListener('change', (e) => {
    handleFiles(e.target.files);
    e.target.value = '';
  });
  updateAttachAvailability();
  updateComposerHint();

  const box = document.getElementById('chatMessages');
  const fab = document.getElementById('scrollBottomFab');
  box?.addEventListener('scroll', () => {
    state.stickToBottom = nearBottom(box);
    fab?.classList.toggle('show', !state.stickToBottom);
  });

  document.addEventListener('click', onClick);
  document.addEventListener('click', (e) => {
    const btn = document.getElementById('modelPickerBtn');
    const dd = document.getElementById('modelDropdown');
    if (btn && dd && !btn.contains(e.target) && !dd.contains(e.target)) dd.classList.remove('open');
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeLightbox();
      document.getElementById('settingsPanel')?.classList.remove('open');
    }
  });

  if (window.innerWidth <= 768) {
    const t = document.getElementById('sidebarToggle');
    if (t) t.style.display = 'flex';
  }

  scrollToBottom();
  textarea?.focus();
}

function onClick(e) {
  const imgEl = e.target.closest('.msg-image');
  if (imgEl) { e.preventDefault(); openLightbox(imgEl.currentSrc || imgEl.src); return; }

  const el = e.target.closest('[data-action]');
  if (!el) return;
  const action = el.dataset.action;
  switch (action) {
    case 'send': sendMessage(); break;
    case 'stop': stopGeneration(); break;
    case 'new-chat': newChat(); break;
    case 'toggle-sidebar': toggleSidebar(); break;
    case 'close-sidebar': toggleSidebar(false); break;
    case 'toggle-models': e.stopPropagation(); toggleModelDropdown(); break;
    case 'scroll-bottom': state.stickToBottom = true; scrollToBottom(); break;
    case 'delete-session': e.preventDefault(); e.stopPropagation(); deleteSession(Number(el.dataset.sessionId)); break;
    case 'pin-session': e.preventDefault(); e.stopPropagation(); pinSession(Number(el.dataset.sessionId)); break;
    case 'rename-session': {
      e.preventDefault(); e.stopPropagation();
      const item = document.querySelector(`.chat-session-item[data-session-id="${el.dataset.sessionId}"]`);
      editTitleInline(item?.querySelector('.chat-session-title'), Number(el.dataset.sessionId));
      break;
    }
    case 'rename-current': {
      e.preventDefault();
      const box = document.getElementById('chatTitleEdit');
      editTitleInline(document.getElementById('chatTitleText'), Number(box?.dataset.sessionId));
      break;
    }
    case 'select-model': selectModel(el.dataset.modelId, el.dataset.modelName); break;
    case 'attach-image': document.getElementById('attachInput')?.click(); break;
    case 'remove-attach': state.attachments.splice(Number(el.dataset.idx), 1); renderPreviews(); break;
    case 'suggestion': {
      const ta = document.getElementById('chatInput');
      ta.value = el.dataset.suggestion || el.textContent.trim();
      ta.dispatchEvent(new Event('input'));
      ta.focus();
      break;
    }
    case 'copy-code': {
      const code = el.closest('.code-block')?.querySelector('pre code');
      if (code) copyText(code.textContent, el);
      break;
    }
    case 'copy-msg': {
      const c = el.closest('.msg')?.querySelector('.msg-content');
      copyText(c?._md ?? c?.textContent ?? '', el);
      break;
    }
    case 'lb-close': closeLightbox(); break;
    case 'lb-download': downloadImage(document.getElementById('lightbox')?._src); break;
    case 'lb-copy': copyImage(document.getElementById('lightbox')?._src); break;
    case 'lb-share': shareImage(document.getElementById('lightbox')?._src); break;
    case 'regenerate': regenerate(); break;
    case 'edit-msg': editUserMessage(el.closest('.msg')); break;
    case 'edit-save': closeEditBox(el.closest('.edit-box'), true); break;
    case 'edit-cancel': closeEditBox(el.closest('.edit-box'), false); break;
    case 'open-settings': e.preventDefault(); openSettings(); break;
    case 'save-settings': saveSettings(); break;
    case 'close-settings': document.getElementById('settingsPanel')?.classList.remove('open'); break;
  }
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
else init();
