/* ============================================================
   Provider / model-vendor brand marks.
   Vendor is derived from the model_id prefix (e.g. "openai/gpt-4o").
   Authentic SVG marks for major vendors; clean brand-coloured
   monograms for the long tail. All theme-aware.
============================================================ */

// model_id prefix -> canonical vendor key
const ALIASES = {
  'meta-llama': 'meta',
  'x-ai': 'xai',
  'z-ai': 'zai',
  'mistralai': 'mistral',
  'moonshotai': 'moonshot',
  'nousresearch': 'nous',
  'ibm-granite': 'ibm',
  'bytedance-seed': 'bytedance',
  'arcee-ai': 'arcee',
  'aion-labs': 'aion',
  'inclusionai': 'inclusion',
  'cognitivecomputations': 'cognitive',
  'anthracite-org': 'anthracite',
  'prime-intellect': 'prime',
  'allenai': 'allen',
  'thedrummer': 'drummer',
};

// Authentic marks (viewBox 0 0 24 24). `mono` => currentColor, coloured by tile.
const OPENAI = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.28 9.82a5.98 5.98 0 0 0-.52-4.91 6.05 6.05 0 0 0-6.51-2.9A6.07 6.07 0 0 0 4.98 4.18a5.98 5.98 0 0 0-3.99 2.9 6.05 6.05 0 0 0 .74 7.1 5.98 5.98 0 0 0 .51 4.91 6.05 6.05 0 0 0 6.51 2.9A5.98 5.98 0 0 0 13.26 22a6.06 6.06 0 0 0 5.77-4.21 5.99 5.99 0 0 0 4-2.9 6.06 6.06 0 0 0-.75-7.07zM13.26 20.6a4.5 4.5 0 0 1-2.89-1.04l.14-.08 4.79-2.77a.78.78 0 0 0 .39-.68V9.29l2.02 1.17a.07.07 0 0 1 .04.05v5.59a4.51 4.51 0 0 1-4.49 4.5zM3.6 16.47a4.49 4.49 0 0 1-.54-3.02l.14.09 4.79 2.76a.78.78 0 0 0 .78 0l5.84-3.37v2.33a.07.07 0 0 1-.03.06L9.75 20.2a4.51 4.51 0 0 1-6.15-1.65zM2.34 6.83a4.49 4.49 0 0 1 2.35-1.97v5.68a.78.78 0 0 0 .39.68l5.82 3.36-2.02 1.16a.07.07 0 0 1-.07 0L3.97 12.9a4.51 4.51 0 0 1-1.63-6.06zm16.6 3.86-5.83-3.39 2.02-1.16a.07.07 0 0 1 .07 0l4.83 2.79a4.5 4.5 0 0 1-.68 8.11v-5.68a.79.79 0 0 0-.4-.67zm2.01-3.02-.14-.09-4.78-2.76a.78.78 0 0 0-.78 0L9.33 6.19V3.86a.07.07 0 0 1 .03-.06l4.83-2.79a4.5 4.5 0 0 1 6.68 4.66zM8.22 12.14 6.2 10.98a.07.07 0 0 1-.04-.06V5.34a4.5 4.5 0 0 1 7.38-3.45l-.14.08-4.79 2.77a.78.78 0 0 0-.39.68l-.01 6.72zm1.1-2.37L11.93 8.26l2.6 1.5v3.01l-2.6 1.5-2.61-1.5V9.78z"/></svg>';
const GEMINI = '<svg viewBox="0 0 24 24"><defs><linearGradient id="vg-gem" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#4285F4"/><stop offset=".5" stop-color="#7C6CE4"/><stop offset="1" stop-color="#D96570"/></linearGradient></defs><path fill="url(#vg-gem)" d="M12 1.5c.32 5.61 4.89 10.18 10.5 10.5-5.61.32-10.18 4.89-10.5 10.5-.32-5.61-4.89-10.18-10.5-10.5C7.11 11.68 11.68 7.11 12 1.5Z"/></svg>';
const META = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.5 6.8C4.2 6.8 2.7 9 2.7 12s1.5 5.2 3.7 5.2c1.6 0 2.9-.9 4.1-2.5.5-.7 1-1.5 1.6-2.5.6 1 1.1 1.8 1.6 2.5 1.2 1.6 2.5 2.5 4.1 2.5 2.3 0 3.8-2.2 3.8-5.2s-1.5-5.2-3.8-5.2c-1.6 0-3 .9-4.2 2.6l-.1.2-.1-.2C11.9 7.7 10.5 6.8 8.9 6.8h-.2Zm.2 2.1c.8 0 1.6.6 2.5 1.9l.6.9-.7 1c-.9 1.4-1.7 2.1-2.6 2.1-1.1 0-1.8-1.1-1.8-2.9S5.6 8.9 6.7 8.9Zm10.6 0c1.1 0 1.8 1.1 1.8 2.9s-.7 2.9-1.8 2.9c-.8 0-1.6-.6-2.5-1.9l-.7-1 .6-.9c.9-1.3 1.7-1.9 2.6-1.9Z"/></svg>';
const XAI = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 3h3.6l4.4 6 4.4-6H20l-6.2 8.4L20.4 21h-3.6l-4.8-6.6L7.2 21H3.6l6.6-9L4 3Z"/></svg>';
const MICROSOFT = '<svg viewBox="0 0 24 24"><rect x="2.5" y="2.5" width="9" height="9" fill="#F25022"/><rect x="12.5" y="2.5" width="9" height="9" fill="#7FBA00"/><rect x="2.5" y="12.5" width="9" height="9" fill="#00A4EF"/><rect x="12.5" y="12.5" width="9" height="9" fill="#FFB900"/></svg>';
const ANTHROPIC = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.4 3h-3.02L18.5 21h3.04L15.4 3Zm-6.8 0L2.46 21H5.6l1.25-3.63h6.02L14.1 21h.04L8.62 3H8.6Zm-.72 11.6 2.02-5.85 2.01 5.85H7.88Z"/></svg>';

// key -> { label, color, svg? }  (color CSS value; var() allowed)
const VENDORS = {
  openai:     { label: 'OpenAI',     color: 'var(--text-strong)', svg: OPENAI },
  google:     { label: 'Google',     color: null,                 svg: GEMINI },
  meta:       { label: 'Meta',       color: '#0866FF',            svg: META },
  microsoft:  { label: 'Microsoft',  color: null,                 svg: MICROSOFT },
  xai:        { label: 'xAI',        color: 'var(--text-strong)', svg: XAI },
  anthropic:  { label: 'Anthropic',  color: '#D97757',            svg: ANTHROPIC },
  mistral:    { label: 'Mistral',    color: '#FA520F' },
  qwen:       { label: 'Qwen',       color: '#615CED' },
  deepseek:   { label: 'DeepSeek',   color: '#4D6BFE' },
  zai:        { label: 'Z.AI',       color: '#3859FF' },
  nvidia:     { label: 'NVIDIA',     color: '#76B900' },
  minimax:    { label: 'MiniMax',    color: '#F23F5D' },
  moonshot:   { label: 'Moonshot',   color: '#16162B', darkColor: '#8E8EF0' },
  cohere:     { label: 'Cohere',     color: '#39594D' },
  amazon:     { label: 'Amazon',     color: '#FF9900' },
  perplexity: { label: 'Perplexity', color: '#20808D' },
  nous:       { label: 'Nous',       color: '#111827', darkColor: '#9CA3AF' },
  liquid:     { label: 'Liquid',     color: '#0F172A', darkColor: '#94A3B8' },
  reka:       { label: 'Reka',       color: '#5B21B6' },
  ai21:       { label: 'AI21',       color: '#E4447C' },
  ibm:        { label: 'IBM',        color: '#0F62FE' },
  bytedance:  { label: 'ByteDance',  color: '#325AB4' },
  tencent:    { label: 'Tencent',    color: '#0052D9' },
  baidu:      { label: 'Baidu',      color: '#2932E1' },
  xiaomi:     { label: 'Xiaomi',     color: '#FF6900' },
  stepfun:    { label: 'StepFun',    color: '#005CFA' },
  inflection: { label: 'Inflection', color: '#6D28D9' },
  writer:     { label: 'Writer',     color: '#5A3FF2' },
  upstage:    { label: 'Upstage',    color: '#8B5CF6' },
  allen:      { label: 'Ai2',        color: '#F0529C' },
  arcee:      { label: 'Arcee',      color: '#0EA5E9' },
  openrouter: { label: 'OpenRouter', color: '#6B7280' },
};

const FALLBACK_PALETTE = ['#EF4444','#F59E0B','#10B981','#3B82F6','#8B5CF6','#EC4899','#14B8A6','#F97316','#6366F1','#84CC16'];

export function vendorKey(modelId) {
  if (!modelId) return '';
  let p = String(modelId).split('/')[0].toLowerCase().replace(/^~/, '');
  return ALIASES[p] || p;
}

function pickColor(str) {
  let sum = 0;
  for (let i = 0; i < str.length; i++) sum += str.charCodeAt(i);
  return FALLBACK_PALETTE[sum % FALLBACK_PALETTE.length];
}

function isDark() {
  return document.documentElement.getAttribute('data-theme') === 'dark';
}

export function vendorInfo(key) {
  const v = VENDORS[key];
  if (v) {
    const color = (isDark() && v.darkColor) ? v.darkColor : (v.color || pickColor(key));
    return { label: v.label, color, svg: v.svg };
  }
  return { label: key ? key.charAt(0).toUpperCase() + key.slice(1) : 'AI', color: pickColor(key || 'ai'), svg: null };
}

function initials(label) {
  const clean = (label || 'AI').replace(/[^A-Za-z0-9]/g, '');
  return (clean.slice(0, 1) || 'A').toUpperCase();
}

/** HTML string for a 1-line vendor mark tile. size in px. */
export function vendorMark(modelId, size = 24) {
  const key = vendorKey(modelId);
  const info = vendorInfo(key);
  const s = `width:${size}px;height:${size}px;`;
  if (info.svg) {
    return `<span class="vmark" style="${s}color:${info.color}">${info.svg}</span>`;
  }
  return `<span class="vmark vmark-mono" style="${s}background:${info.color}">${initials(info.label)}</span>`;
}

export function vendorName(modelId) {
  return vendorInfo(vendorKey(modelId)).label;
}
