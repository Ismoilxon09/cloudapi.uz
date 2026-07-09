@extends('layouts.app')

@section('title', 'CloudAPI — Unified AI API Gateway')

@push('styles')
<style>
/* ===== NAV ===== */
.landing-nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  background: rgba(255, 255, 255, .72);
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
  border-bottom: 1px solid var(--border);
  height: 64px; display: flex; align-items: center; padding: 0 32px;
}
[data-theme="dark"] .landing-nav { background: rgba(10, 10, 10, .72); }

.landing-nav-inner {
  display: flex; align-items: center; justify-content: space-between;
  width: 100%; max-width: 1280px; margin: 0 auto;
}

/* ===== GLOBAL CURSOR EFFECTS (entire page) ===== */
.global-fx {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
}

.global-fx canvas {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
}

/* Subtle dot grid — fixed across entire viewport */
.dot-grid {
  position: fixed;
  inset: 0;
  z-index: 0;
  background-image: radial-gradient(circle, var(--border) 1px, transparent 1px);
  background-size: 32px 32px;
  mask-image: radial-gradient(ellipse 80% 60% at 50% 30%, black, transparent 90%);
  -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 30%, black, transparent 90%);
  opacity: .35;
  pointer-events: none;
}

/* ===== HERO ===== */
.hero-wrap {
  position: relative;
  min-height: 100vh;
  display: flex; align-items: center; justify-content: center;
  padding: 100px 32px 60px;
  overflow: hidden;
  background: transparent;
}

.hero-content {
  position: relative; z-index: 2;
  text-align: center; max-width: 1100px; margin: 0 auto;
  pointer-events: none;
}
.hero-content > * { pointer-events: auto; }

.hero-badge {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 6px 14px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 99px;
  font-size: 12px; font-weight: 500; color: var(--text-muted);
  margin-bottom: 28px;
  box-shadow: var(--shadow-sm);
  animation: fadeUp .8s var(--ease-spring) both;
}

.hero-badge::before {
  content: ''; width: 6px; height: 6px;
  background: var(--success); border-radius: 50%;
  box-shadow: 0 0 8px var(--success);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: .5; transform: scale(1.4); }
}

.hero-title {
  font-size: clamp(40px, 7vw, 80px);
  font-weight: 800; letter-spacing: -0.04em;
  line-height: 1; margin-bottom: 28px;
  color: var(--text-strong);
  animation: fadeUp .8s .1s var(--ease-spring) both;
}

.hero-title-accent {
  color: var(--text-muted);
  font-weight: 700;
  display: inline-block;
  position: relative;
}

.hero-title-accent::after {
  content: '';
  position: absolute;
  bottom: 8px;
  left: 0;
  right: 0;
  height: 12px;
  background: var(--accent);
  opacity: 0.15;
  z-index: -1;
  border-radius: 4px;
}

.hero-subtitle {
  font-size: 18px;
  color: var(--text-muted);
  max-width: 620px;
  margin: 0 auto 44px;
  line-height: 1.6;
  animation: fadeUp .8s .2s var(--ease-spring) both;
}

.hero-cta {
  display: flex; align-items: center; justify-content: center;
  gap: 12px; flex-wrap: wrap;
  animation: fadeUp .8s .3s var(--ease-spring) both;
}

.hero-meta {
  display: flex; align-items: center; justify-content: center;
  gap: 32px; margin-top: 40px;
  font-size: 13px; color: var(--text-muted);
  animation: fadeUp .8s .4s var(--ease-spring) both;
}

.hero-meta-item { display: flex; align-items: center; gap: 6px; }
.hero-meta-item .material-icons-round { font-size: 16px; color: var(--success); }

/* ===== CODE PREVIEW ===== */
.code-preview {
  max-width: 780px;
  margin: 60px auto 0;
  background: #0A0A0A;
  border: 1px solid #1F2937;
  border-radius: 14px;
  box-shadow: 0 30px 80px rgba(0, 0, 0, .25);
  text-align: left;
  overflow: hidden;
  animation: fadeUp 1s .5s var(--ease-spring) both;
  color: #E5E7EB;
}

[data-theme="dark"] .code-preview {
  background: #000;
}

.code-header {
  display: flex; align-items: center; gap: 8px;
  padding: 14px 18px;
  border-bottom: 1px solid #1F2937;
  background: #111111;
}

.code-dot { width: 10px; height: 10px; border-radius: 50%; }

.code-tabs { display: flex; gap: 4px; margin-left: 20px; }
.code-tab {
  padding: 5px 14px;
  font-size: 11px; font-weight: 500;
  color: #6B7280;
  border-radius: 6px;
  cursor: pointer;
  transition: all .15s;
}
.code-tab.active {
  background: #1F2937;
  color: #F9FAFB;
}

.code-content {
  padding: 24px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 13px;
  line-height: 1.8;
  overflow-x: auto;
  white-space: pre;
  color: #D1D5DB;
}

.code-content .kw { color: #93C5FD; }
.code-content .str { color: #86EFAC; }
.code-content .com { color: #6B7280; font-style: italic; }
.code-content .var { color: #FCD34D; }
.code-content .fn { color: #F0ABFC; }

.hero-scroll {
  position: absolute; bottom: 24px; left: 50%;
  z-index: 2;
  transform: translateX(-50%);
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  color: var(--text-subtle);
  font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em;
  animation: bounceDown 2s ease-in-out infinite;
  pointer-events: none; z-index: 5;
}
@keyframes bounceDown {
  0%, 100% { transform: translateX(-50%) translateY(0); }
  50% { transform: translateX(-50%) translateY(8px); }
}

/* ===== SECTIONS ===== */
.section {
  padding: 120px 32px;
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
  z-index: 2;
}

.section-header {
  text-align: center;
  margin-bottom: 72px;
}

.section-eyebrow {
  display: inline-block;
  font-size: 12px; font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase; letter-spacing: 0.12em;
  margin-bottom: 14px;
  padding: 5px 14px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 99px;
}

.section-title {
  font-size: clamp(32px, 5vw, 52px);
  font-weight: 800; letter-spacing: -0.03em;
  margin-bottom: 18px; line-height: 1.05;
  color: var(--text-strong);
}

.section-subtitle {
  font-size: 17px;
  color: var(--text-muted);
  max-width: 640px;
  margin: 0 auto;
  line-height: 1.6;
}

/* ===== HOW IT WORKS — 4 steps ===== */
.steps-wrap {
  max-width: 1100px;
  margin: 0 auto;
}

.steps-line {
  position: relative;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.step-card {
  background: var(--bg-elevated);
  z-index: 1;
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 28px 24px;
  transition: all .3s var(--ease-spring);
  opacity: 0;
  transform: translateY(30px);
  position: relative;
}

.step-card.in-view {
  opacity: 1;
  transform: translateY(0);
}

.step-card:hover {
  border-color: var(--text-muted);
  box-shadow: var(--shadow-md);
  transform: translateY(-4px);
}

.step-num {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px;
  background: var(--gray-deep);
  color: white;
  border-radius: 8px;
  font-size: 13px; font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  margin-bottom: 20px;
}

.step-title {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--text-strong);
}

.step-desc {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.6;
}

.step-arrow {
  position: absolute;
  top: 38px;
  right: -24px;
  color: var(--border-strong);
  z-index: 1;
}

.step-arrow .material-icons-round { font-size: 20px; }

/* ===== ABOUT SECTION ===== */
.about-wrap {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 64px;
  align-items: center;
  max-width: 1100px;
  margin: 0 auto;
}

.about-content h2 {
  font-size: 44px;
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.1;
  margin-bottom: 20px;
  color: var(--text-strong);
}

.about-content p {
  font-size: 15px;
  color: var(--text-muted);
  line-height: 1.7;
  margin-bottom: 16px;
}

.about-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
  margin-top: 32px;
  padding-top: 32px;
  border-top: 1px solid var(--border);
}

.about-stat-num {
  font-size: 32px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.about-stat-label {
  font-size: 12px;
  color: var(--text-muted);
  font-weight: 500;
}

.about-visual {
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 40px;
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

/* ===== FEATURES ===== */
.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 16px;
}

.feature-card {
  background: var(--bg-elevated);
  position: relative;
  z-index: 1;
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 28px;
  transition: all .3s var(--ease-spring);
  position: relative;
  opacity: 0;
  transform: translateY(30px);
}

.feature-card.in-view {
  opacity: 1; transform: translateY(0);
}

.feature-card:hover {
  border-color: var(--text-muted);
  box-shadow: var(--shadow-md);
  transform: translateY(-4px);
}

.feature-icon {
  width: 44px; height: 44px;
  background: var(--gray-deep);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 20px;
  color: white;
}

.feature-icon .material-icons-round { font-size: 22px; }

.feature-title {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--text-strong);
}

.feature-desc {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.65;
}

/* ===== MODELS ===== */
.models-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
  margin-top: 40px;
}

.model-card {
  background: var(--bg-elevated);
  z-index: 1;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all .2s var(--ease);
  opacity: 0;
  transform: translateY(20px);
}

.model-card.in-view {
  opacity: 1; transform: translateY(0);
}

.model-card:hover {
  border-color: var(--text-muted);
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.model-logo {
  width: 38px; height: 38px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
}

.model-logo svg { width: 22px; height: 22px; }

.model-name { font-size: 13px; font-weight: 600; color: var(--text-strong); }
.model-meta { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

/* ===== CTA ===== */
.cta-section {
  position: relative;
  z-index: 2;
  margin: 120px auto;
  max-width: 1100px;
  padding: 80px 48px;
  background: #0a0a0a;
  color: white;
  border-radius: 24px;
  text-align: center;
  overflow: hidden;
  border: 1px solid #262626;
}

[data-theme="dark"] .cta-section {
  background: #ffffff;
  color: #0a0a0a;
  border: 1px solid #262626;
}

[data-theme="dark"] .cta-section h2 {
  color: #0a0a0a !important;
}

[data-theme="dark"] .cta-section p {
  color: rgba(10, 10, 10, .65) !important;
}

[data-theme="dark"] .cta-section .btn[style*="background:white"] {
  background: #0a0a0a !important;
  color: white !important;
}

[data-theme="dark"] .cta-section .btn[style*="background:transparent"] {
  color: #0a0a0a !important;
  border-color: rgba(10, 10, 10, .2) !important;
}
.cta-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
}

.cta-section h2 {
  color: white;
  font-size: clamp(28px, 4vw, 44px);
  font-weight: 800;
  letter-spacing: -0.03em;
  margin-bottom: 18px;
}

.cta-section p {
  color: rgba(255, 255, 255, .65);
  font-size: 17px;
  max-width: 540px;
  margin: 0 auto;
  line-height: 1.6;
}

/* ===== FOOTER ===== */
.footer {
  position: relative;
  z-index: 2;
  padding: 60px 32px 32px;
  border-top: 1px solid var(--border);
  background: var(--bg-subtle);
}

.footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 48px;
}

.footer-brand {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.6;
  margin-top: 14px;
  max-width: 320px;
}

.footer-col h4 {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 16px;
  color: var(--text-strong);
}

.footer-col a {
  display: block;
  font-size: 13px;
  color: var(--text-muted);
  padding: 5px 0;
  transition: color .15s;
}

.footer-col a:hover { color: var(--text-strong); }

.footer-bottom {
  max-width: 1200px;
  margin: 40px auto 0;
  padding-top: 28px;
  border-top: 1px solid var(--border);
  font-size: 12px;
  color: var(--text-muted);
  display: flex;
  justify-content: space-between;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 900px) {
  .steps-line { grid-template-columns: 1fr 1fr; }
  .step-arrow { display: none; }
  .about-wrap { grid-template-columns: 1fr; gap: 40px; }
  .footer-inner { grid-template-columns: 1fr 1fr; }
  .cta-section {
  position: relative;
  z-index: 2; padding: 56px 24px; margin: 80px 16px; }
}

@media (max-width: 640px) {
  .steps-line { grid-template-columns: 1fr; }
  .hero-meta { flex-direction: column; gap: 12px; }
}

/* ============================================================
   YANGI QO'SHILGAN — enhanced effects + mobile + feedbacks
============================================================ */

/* Simlarni yashirish (agar bo'lsa) */
.orbit-svg, .floating-line, .hero-line, .hero-connector,
.hero-orbit, .orbit-line, .orbit-path, .decorative-line { display: none !important; }

/* Enhanced button shine */
.btn-primary { position: relative; overflow: hidden; }
.btn-primary::before {
  content: ''; position: absolute; top: 0; left: -100%;
  width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
  transition: left .6s ease;
}
.btn-primary:hover::before { left: 100%; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(0,0,0,.15); }

/* Enhanced feature cards */
.feature-card:hover .feature-icon { transform: scale(1.08) rotate(-3deg); }
.feature-icon { transition: transform .3s ease; }

/* Enhanced hero bg */
.hero-wrap::before {
  content: ''; position: absolute; top: -50%; left: -20%;
  width: 60%; height: 100%;
  background: radial-gradient(circle, rgba(124,58,237,.04) 0%, transparent 60%);
  pointer-events: none; z-index: 0;
  animation: bgFloat1 20s ease-in-out infinite;
}
.hero-wrap::after {
  content: ''; position: absolute; bottom: -50%; right: -20%;
  width: 60%; height: 100%;
  background: radial-gradient(circle, rgba(236,72,153,.04) 0%, transparent 60%);
  pointer-events: none; z-index: 0;
  animation: bgFloat2 25s ease-in-out infinite;
}
@keyframes bgFloat1 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(30px,40px); } }
@keyframes bgFloat2 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-40px,-30px); } }

/* ===== FEEDBACKS ===== */
.feedbacks-section { padding: 100px 32px; background: var(--bg); position: relative; z-index: 2; }
.feedbacks-container { max-width: 1200px; margin: 0 auto; }
.feedbacks-header { text-align: center; margin-bottom: 48px; }
.feedbacks-badge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 6px 14px; background: var(--bg-subtle);
  border: 1px solid var(--border); border-radius: 999px;
  font-size: 12px; font-weight: 600; color: var(--text-muted);
  margin-bottom: 16px; text-transform: uppercase; letter-spacing: .05em;
}
.feedbacks-badge .material-icons-round { font-size: 16px; }
.feedbacks-title { font-size: 40px; font-weight: 800; letter-spacing: -.03em; color: var(--text-strong); margin: 0 0 12px; line-height: 1.15; }
.feedbacks-subtitle { font-size: 16px; color: var(--text-muted); max-width: 560px; margin: 0 auto 24px; line-height: 1.5; }
.feedbacks-stats { display: flex; gap: 40px; justify-content: center; margin-top: 20px; }
.feedbacks-stat-value { font-size: 28px; font-weight: 800; color: var(--text-strong); letter-spacing: -.02em; display: flex; align-items: center; justify-content: center; gap: 4px; }
.feedbacks-stat-label { font-size: 12.5px; color: var(--text-muted); margin-top: 2px; }
.feedbacks-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 60px; }
.feedback-card {
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: 14px; padding: 22px; transition: all .2s;
  display: flex; flex-direction: column;
}
.feedback-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,.08); border-color: var(--border-strong); }
.feedback-header { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.feedback-avatar {
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white; font-weight: 700; font-size: 15px; flex-shrink: 0;
}
.feedback-user { flex: 1; min-width: 0; }
.feedback-name { font-weight: 700; font-size: 13.5px; color: var(--text-strong); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.feedback-date { font-size: 11.5px; color: var(--text-muted); margin-top: 1px; }
.feedback-rating { display: flex; gap: 1px; }
.feedback-text { font-size: 13.5px; line-height: 1.55; color: var(--text); margin-bottom: 12px; flex: 1; }
.feedback-reply {
  padding: 12px 14px; background: var(--bg-subtle);
  border-left: 3px solid var(--text-strong);
  border-radius: 0 8px 8px 0; margin-top: 12px;
}
.feedback-reply-header { display: flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-bottom: 6px; }
.feedback-reply-header .material-icons-round { font-size: 14px; }
.feedback-reply-text { font-size: 12.5px; color: var(--text); line-height: 1.5; font-style: italic; }
.feedback-cta { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 20px; padding: 40px; text-align: center; margin-top: 40px; }
.feedback-cta-title { font-size: 26px; font-weight: 800; letter-spacing: -.02em; color: var(--text-strong); margin: 0 0 8px; }
.feedback-cta-subtitle { color: var(--text-muted); font-size: 14px; margin: 0 auto 24px; max-width: 500px; }
.feedback-cta-buttons { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.fb-btn { display: inline-flex; align-items: center; gap: 6px; padding: 12px 22px; border-radius: 10px; font-size: 14px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; transition: all .15s; }
.fb-btn .material-icons-round { font-size: 18px; }
.fb-btn-primary { background: var(--text-strong); color: var(--bg-elevated); }
.fb-btn-primary:hover { transform: translateY(-1px); opacity: .92; }
.fb-btn-secondary { background: var(--bg-subtle); color: var(--text-strong); border: 1px solid var(--border); }
.fb-btn-secondary:hover { background: var(--bg-elevated); border-color: var(--border-strong); }
.fb-btn-full { width: 100%; justify-content: center; }

/* Feedback Modal */
.fb-modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px); }
.fb-modal.active { display: flex; animation: fadeIn .2s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.fb-modal-content { background: var(--bg-elevated); border-radius: 16px; padding: 32px; max-width: 480px; width: 100%; max-height: 90vh; overflow-y: auto; position: relative; animation: slideUp .3s ease; }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.fb-modal-close { position: absolute; top: 16px; right: 16px; width: 32px; height: 32px; border: none; background: var(--bg-subtle); border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
.fb-modal-close:hover { background: var(--border); color: var(--text-strong); }
.fb-modal-header { margin-bottom: 24px; }
.fb-modal-header h3 { font-size: 22px; font-weight: 800; color: var(--text-strong); margin: 0 0 6px; letter-spacing: -.02em; }
.fb-modal-header p { font-size: 13px; color: var(--text-muted); margin: 0; }
.fb-field { margin-bottom: 18px; }
.fb-field label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }
.fb-input, .fb-textarea { width: 100%; padding: 12px 14px; background: var(--bg-subtle); border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; color: var(--text-strong); font-family: inherit; transition: all .15s; resize: vertical; }
.fb-input:focus, .fb-textarea:focus { outline: none; border-color: var(--text-strong); background: var(--bg-elevated); }
.fb-textarea { min-height: 100px; }
.fb-char-count { font-size: 11px; color: var(--text-subtle); text-align: right; margin-top: 4px; }
.rating-input { display: flex; gap: 4px; }
.rating-star { background: transparent; border: none; cursor: pointer; padding: 4px; transition: transform .15s; }
.rating-star:hover { transform: scale(1.15); }
.rating-star .material-icons-round { font-size: 30px; color: var(--border); transition: color .15s; }
.rating-star.active .material-icons-round { color: #FBBF24; }
.fb-form-message { display: none; padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 14px; font-weight: 500; }
.fb-form-message.show { display: block; }
.fb-form-message.success { background: rgba(16,185,129,.08); border: 1px solid rgba(16,185,129,.2); color: #10B981; }
.fb-form-message.error { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2); color: #EF4444; }
@keyframes spinAnim { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* ===== MOBILE MENU ===== */
.nav-mobile-toggle {
  display: none; background: transparent;
  border: 1px solid var(--border); border-radius: 8px;
  width: 40px; height: 40px; align-items: center; justify-content: center;
  cursor: pointer; color: var(--text-strong); transition: all .15s;
}
.nav-mobile-toggle:hover { background: var(--bg-subtle); }
.nav-mobile-toggle .material-icons-round { font-size: 22px; }
.nav-mobile-menu {
  display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: var(--bg); z-index: 9998; padding: 60px 24px 24px; overflow-y: auto;
}
.nav-mobile-menu.active { display: block; animation: slideDown .2s ease; }
@keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.nav-mobile-close {
  position: absolute; top: 16px; right: 16px;
  width: 40px; height: 40px; background: transparent;
  border: 1px solid var(--border); border-radius: 8px;
  cursor: pointer; color: var(--text-strong);
  display: flex; align-items: center; justify-content: center;
}
.nav-mobile-links { display: flex; flex-direction: column; gap: 4px; margin-bottom: 32px; }
.nav-mobile-link { padding: 16px 20px; font-size: 16px; font-weight: 600; color: var(--text-strong); text-decoration: none; border-radius: 10px; transition: background .15s; border-bottom: 1px solid var(--border); }
.nav-mobile-link:hover { background: var(--bg-subtle); }
.nav-mobile-actions { display: flex; flex-direction: column; gap: 10px; }

/* ===== MOBILE RESPONSIVE ===== */
@media (max-width: 768px) {
  .landing-nav { padding: 0 16px !important; }
  .landing-nav .nav-links { display: none !important; }
  .landing-nav .topbar-actions .btn-ghost,
  .landing-nav .topbar-actions .btn-primary { display: none !important; }
  .nav-mobile-toggle { display: flex !important; }
  .hero-wrap { padding: 80px 16px 60px !important; min-height: auto !important; }
  .hero-title { font-size: clamp(28px, 8vw, 44px) !important; }
  .hero-subtitle { font-size: 14px !important; }
  .hero-cta { flex-direction: column; width: 100%; }
  .hero-cta a { width: 100%; justify-content: center; }
  .hero-meta { flex-direction: column !important; gap: 12px !important; }
  .code-preview { margin-top: 40px !important; }
  .code-content { padding: 16px !important; font-size: 11px !important; }
  .section { padding: 60px 16px !important; }
  .section-header { margin-bottom: 40px !important; }
  .section-title { font-size: clamp(24px, 6vw, 32px) !important; }
  .section-subtitle { font-size: 14px !important; }
  .steps-line { grid-template-columns: 1fr !important; }
  .step-arrow { display: none; }
  .about-wrap { grid-template-columns: 1fr !important; gap: 32px !important; }
  .about-content h2 { font-size: 28px !important; }
  .about-stats { grid-template-columns: repeat(2, 1fr) !important; gap: 16px !important; }
  .about-stat-num { font-size: 24px !important; }
  .features-grid { grid-template-columns: 1fr !important; }
  .feature-card { padding: 22px !important; }
  .models-grid { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
  .model-card { padding: 14px !important; }
  .model-name { font-size: 12px !important; }
  .model-meta { font-size: 10px !important; }
  .cta-section { padding: 48px 20px !important; margin: 60px 16px !important; }
  .cta-section h2 { font-size: 26px !important; }
  .cta-section p { font-size: 14px !important; }
  .footer { padding: 40px 20px 24px !important; }
  .footer-inner { grid-template-columns: 1fr !important; gap: 28px !important; }
  .footer-bottom { flex-direction: column; text-align: center; gap: 12px; }

  .feedbacks-section { padding: 60px 16px !important; }
  .feedbacks-title { font-size: 26px !important; }
  .feedbacks-subtitle { font-size: 14px !important; }
  .feedbacks-stats { gap: 24px; }
  .feedbacks-stat-value { font-size: 22px !important; }
  .feedbacks-grid { grid-template-columns: 1fr !important; gap: 14px !important; }
  .feedback-card { padding: 18px !important; }
  .feedback-cta { padding: 28px 20px !important; }
  .feedback-cta-title { font-size: 22px !important; }
  .feedback-cta-buttons { flex-direction: column; }
  .fb-btn { justify-content: center; }
  .fb-modal-content { padding: 24px !important; }
}

@media (max-width: 480px) {
  .hero-title { font-size: 26px !important; }
  .models-grid { grid-template-columns: 1fr !important; }
  .about-stats { grid-template-columns: 1fr !important; }
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
      <a href="#how" class="nav-link">{{ __('landing.nav.features') }}</a>
      <a href="#models" class="nav-link">{{ __('landing.nav.models') }}</a>
      <a href="#chat" class="nav-link">Chat</a>
      <a href="{{ route('pricing') }}" class="nav-link">{{ __('landing.nav.pricing') }}</a>
      <a href="{{ route('docs') }}" class="nav-link">{{ __('landing.nav.docs') }}</a>
    </nav>
    <div class="topbar-actions">
      @include('partials.lang-switcher')
      <button class="icon-btn" onclick="toggleTheme()"><span class="material-icons-round" id="themeIcon">dark_mode</span></button>
      <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">{{ __('landing.nav.sign_in') }}</a>
      <a href="{{ route('register') }}" class="btn btn-primary btn-sm">{{ __('landing.nav.get_started') }}</a>
      <button class="nav-mobile-toggle" onclick="openMobileMenu()">
        <span class="material-icons-round">menu</span>
      </button>
    </div>
  </div>
</header>
@endguest

<!-- MOBILE MENU (768px dan pastki ekranlarda) -->
<div class="nav-mobile-menu" id="mobileMenu">
  <button class="nav-mobile-close" onclick="closeMobileMenu()">
    <span class="material-icons-round">close</span>
  </button>
  <div class="nav-mobile-links">
    <a href="#how" class="nav-mobile-link" onclick="closeMobileMenu()">{{ __('landing.nav.features') }}</a>
    <a href="#models" class="nav-mobile-link" onclick="closeMobileMenu()">{{ __('landing.nav.models') }}</a>
    <a href="#chat" class="nav-mobile-link" onclick="closeMobileMenu()">Chat</a>
    <a href="{{ route('pricing') }}" class="nav-mobile-link">{{ __('landing.nav.pricing') }}</a>
    <a href="{{ route('docs') }}" class="nav-mobile-link">{{ __('landing.nav.docs') }}</a>
    <a href="#feedbacks" class="nav-mobile-link" onclick="closeMobileMenu()">Fikrlar</a>
  </div>
  <div class="nav-mobile-actions">
    @auth
      <a href="{{ route('dashboard') }}" class="btn btn-primary btn-full">Dashboard</a>
    @else
      <a href="{{ route('login') }}" class="btn btn-secondary btn-full">{{ __('landing.nav.sign_in') }}</a>
      <a href="{{ route('register') }}" class="btn btn-primary btn-full">{{ __('landing.nav.get_started') }}</a>
    @endauth
  </div>
</div>


<!-- GLOBAL CURSOR EFFECTS (entire page) -->
<div class="dot-grid"></div>
<div class="global-fx">
  <!-- <canvas id="wireCanvas"></canvas> -->
  <canvas id="netCanvas"></canvas>
</div>

<!-- HERO -->
<section class="hero-wrap">
  <div class="hero-content">
    <div class="hero-badge">{{ __('landing.hero.badge') }}</div>

    <h1 class="hero-title">
      {{ __('landing.hero.title_1') }}<br>
      <span class="hero-title-accent">{{ __('landing.hero.title_2') }}</span>
    </h1>

    <p class="hero-subtitle">{{ __('landing.hero.subtitle') }}</p>

    <div class="hero-cta">
      <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
        {{ __('landing.hero.cta_primary') }}
        <span class="material-icons-round">arrow_forward</span>
      </a>
      <a href="{{ route('docs') }}" class="btn btn-secondary btn-lg">
        <span class="material-icons-round">code</span>
        {{ __('landing.hero.cta_secondary') }}
      </a>
    </div>

    <div class="hero-meta">
      <div class="hero-meta-item">
        <span class="material-icons-round">check_circle</span>
        {{ __('landing.hero.meta_1') }}
      </div>
      <div class="hero-meta-item">
        <span class="material-icons-round">check_circle</span>
        {{ __('landing.hero.meta_2') }}
      </div>
      <div class="hero-meta-item">
        <span class="material-icons-round">check_circle</span>
        {{ __('landing.hero.meta_3') }}
      </div>
    </div>

    <div class="code-preview">
      <div class="code-header">
        <div class="code-dot" style="background:#EF4444"></div>
        <div class="code-dot" style="background:#F59E0B"></div>
        <div class="code-dot" style="background:#10B981"></div>
        <div class="code-tabs">
          <div class="code-tab active" data-lang="curl">curl</div>
          <div class="code-tab" data-lang="python">Python</div>
          <div class="code-tab" data-lang="node">Node.js</div>
        </div>
      </div>
      <div class="code-content" id="codeBlock"></div>
    </div>
  </div>

  <div class="hero-scroll">
    <span>Scroll</span>
    <span class="material-icons-round" style="font-size:18px">expand_more</span>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" id="how">
  <div class="section-header">
    <div class="section-eyebrow">{{ __('landing.steps.eyebrow') }}</div>
    <h2 class="section-title">{{ __('landing.steps.title') }}</h2>
    <p class="section-subtitle">{{ __('landing.steps.subtitle') }}</p>
  </div>

  <div class="steps-wrap">
    <div class="steps-line">
      <div class="step-card" data-reveal>
        <div class="step-num">01</div>
        <div class="step-title">{{ __('landing.steps.step_1_title') }}</div>
        <div class="step-desc">{{ __('landing.steps.step_1_desc') }}</div>
        <div class="step-arrow"><span class="material-icons-round">arrow_forward</span></div>
      </div>
      <div class="step-card" data-reveal>
        <div class="step-num">02</div>
        <div class="step-title">{{ __('landing.steps.step_2_title') }}</div>
        <div class="step-desc">{{ __('landing.steps.step_2_desc') }}</div>
        <div class="step-arrow"><span class="material-icons-round">arrow_forward</span></div>
      </div>
      <div class="step-card" data-reveal>
        <div class="step-num">03</div>
        <div class="step-title">{{ __('landing.steps.step_3_title') }}</div>
        <div class="step-desc">{{ __('landing.steps.step_3_desc') }}</div>
        <div class="step-arrow"><span class="material-icons-round">arrow_forward</span></div>
      </div>
      <div class="step-card" data-reveal>
        <div class="step-num">04</div>
        <div class="step-title">{{ __('landing.steps.step_4_title') }}</div>
        <div class="step-desc">{{ __('landing.steps.step_4_desc') }}</div>
      </div>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section class="section" id="about">
  <div class="about-wrap">
    <div class="about-content">
      <div class="section-eyebrow" style="margin-bottom:20px">{{ __('landing.about.eyebrow') }}</div>
      <h2>{{ __('landing.about.title') }}</h2>
      <p>{{ __('landing.about.p1') }}</p>
      <p>{{ __('landing.about.p2') }}</p>

      <div class="about-stats">
        <div>
          <div class="about-stat-num">100+</div>
          <div class="about-stat-label">{{ __('landing.stats.models') }}</div>
        </div>
        <div>
          <div class="about-stat-num">99.9%</div>
          <div class="about-stat-label">{{ __('landing.stats.uptime') }}</div>
        </div>
        <div>
          <div class="about-stat-num">&lt;200ms</div>
          <div class="about-stat-label">{{ __('landing.stats.latency') }}</div>
        </div>
        <div>
          <div class="about-stat-num">24/7</div>
          <div class="about-stat-label">{{ __('landing.about.support_label') }}</div>
        </div>
      </div>
    </div>

    <div class="about-visual">
      <svg viewBox="0 0 320 320" style="width:100%;height:100%" fill="none">
        <!-- Center hub -->
        <circle cx="160" cy="160" r="36" fill="var(--text-strong)"/>
        <text x="160" y="167" text-anchor="middle" fill="white" font-family="Inter" font-weight="700" font-size="14">API</text>

        <!-- Orbiting nodes -->
        <g opacity="0.9">
          <line x1="160" y1="160" x2="60" y2="60" stroke="var(--border-strong)" stroke-width="1"/>
          <line x1="160" y1="160" x2="260" y2="60" stroke="var(--border-strong)" stroke-width="1"/>
          <line x1="160" y1="160" x2="60" y2="260" stroke="var(--border-strong)" stroke-width="1"/>
          <line x1="160" y1="160" x2="260" y2="260" stroke="var(--border-strong)" stroke-width="1"/>
          <line x1="160" y1="160" x2="20" y2="160" stroke="var(--border-strong)" stroke-width="1"/>
          <line x1="160" y1="160" x2="300" y2="160" stroke="var(--border-strong)" stroke-width="1"/>
          <line x1="160" y1="160" x2="160" y2="20" stroke="var(--border-strong)" stroke-width="1"/>
          <line x1="160" y1="160" x2="160" y2="300" stroke="var(--border-strong)" stroke-width="1"/>
        </g>

        <!-- Node circles -->
        <g>
          <circle cx="60" cy="60" r="14" fill="var(--bg-elevated)" stroke="var(--border-strong)" stroke-width="1.5"/>
          <circle cx="260" cy="60" r="14" fill="var(--bg-elevated)" stroke="var(--border-strong)" stroke-width="1.5"/>
          <circle cx="60" cy="260" r="14" fill="var(--bg-elevated)" stroke="var(--border-strong)" stroke-width="1.5"/>
          <circle cx="260" cy="260" r="14" fill="var(--bg-elevated)" stroke="var(--border-strong)" stroke-width="1.5"/>
          <circle cx="20" cy="160" r="14" fill="var(--bg-elevated)" stroke="var(--border-strong)" stroke-width="1.5"/>
          <circle cx="300" cy="160" r="14" fill="var(--bg-elevated)" stroke="var(--border-strong)" stroke-width="1.5"/>
          <circle cx="160" cy="20" r="14" fill="var(--bg-elevated)" stroke="var(--border-strong)" stroke-width="1.5"/>
          <circle cx="160" cy="300" r="14" fill="var(--bg-elevated)" stroke="var(--border-strong)" stroke-width="1.5"/>
        </g>

        <!-- Animated pulses -->
        <circle cx="60" cy="60" r="14" fill="none" stroke="var(--accent)" stroke-width="2">
          <animate attributeName="r" from="14" to="22" dur="2s" repeatCount="indefinite"/>
          <animate attributeName="opacity" from="0.8" to="0" dur="2s" repeatCount="indefinite"/>
        </circle>
        <circle cx="260" cy="260" r="14" fill="none" stroke="var(--accent)" stroke-width="2">
          <animate attributeName="r" from="14" to="22" dur="2s" begin="0.5s" repeatCount="indefinite"/>
          <animate attributeName="opacity" from="0.8" to="0" dur="2s" begin="0.5s" repeatCount="indefinite"/>
        </circle>
        <circle cx="300" cy="160" r="14" fill="none" stroke="var(--accent)" stroke-width="2">
          <animate attributeName="r" from="14" to="22" dur="2s" begin="1s" repeatCount="indefinite"/>
          <animate attributeName="opacity" from="0.8" to="0" dur="2s" begin="1s" repeatCount="indefinite"/>
        </circle>
        <circle cx="160" cy="20" r="14" fill="none" stroke="var(--accent)" stroke-width="2">
          <animate attributeName="r" from="14" to="22" dur="2s" begin="1.5s" repeatCount="indefinite"/>
          <animate attributeName="opacity" from="0.8" to="0" dur="2s" begin="1.5s" repeatCount="indefinite"/>
        </circle>
      </svg>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section" id="features">
  <div class="section-header">
    <div class="section-eyebrow">{{ __('landing.features.eyebrow') }}</div>
    <h2 class="section-title">{{ __('landing.features.title') }}</h2>
    <p class="section-subtitle">{{ __('landing.features.subtitle') }}</p>
  </div>

  <div class="features-grid">
    @foreach(['unified' => 'bolt', 'security' => 'shield', 'payments' => 'payments', 'analytics' => 'insights', 'pricing' => 'trending_up', 'support' => 'support_agent'] as $key => $icon)
      <div class="feature-card" data-reveal>
        <div class="feature-icon"><span class="material-icons-round">{{ $icon }}</span></div>
        <div class="feature-title">{{ __("landing.features.items.{$key}.title") }}</div>
        <div class="feature-desc">{{ __("landing.features.items.{$key}.desc") }}</div>
      </div>
    @endforeach
  </div>
</section>

<!-- MODELS with real logos -->
<section class="section" id="models">
  <div class="section-header">
    <div class="section-eyebrow">{{ __('landing.models.eyebrow') }}</div>
    <h2 class="section-title">{{ __('landing.models.title') }}</h2>
    <p class="section-subtitle">{{ __('landing.models.subtitle') }}</p>
  </div>

  <div class="models-grid">
    <div class="model-card" data-reveal>
      <div class="model-logo">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.682zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/></svg>
      </div>
      <div><div class="model-name">GPT-4o</div><div class="model-meta">OpenAI</div></div>
    </div>

    <div class="model-card" data-reveal>
      <div class="model-logo">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.304 3.541h-3.672l6.696 16.918H24L17.304 3.541zm-10.608 0L0 20.459h3.744l1.37-3.553h7.005l1.369 3.553h3.744L10.536 3.541H6.696zm-.371 10.223L8.616 7.82l2.291 5.945H6.325z"/></svg>
      </div>
      <div><div class="model-name">Claude 3.5 Sonnet</div><div class="model-meta">Anthropic</div></div>
    </div>

    <div class="model-card" data-reveal>
      <div class="model-logo">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L8 12h8l-4-10zm-4 12l4 10 4-10H8z" opacity=".4"/><path d="M12 2L7 7l5 5 5-5-5-5zm-7 7l-3 3 3 3 3-3-3-3zm14 0l-3 3 3 3 3-3-3-3zm-7 7l-5 5 5 5 5-5-5-5z"/></svg>
      </div>
      <div><div class="model-name">Gemini Pro 1.5</div><div class="model-meta">Google DeepMind</div></div>
    </div>

    <div class="model-card" data-reveal>
      <div class="model-logo">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0L1.5 6v12L12 24l10.5-6V6L12 0zm8.5 16.84L12 21.68l-8.5-4.84V7.16L12 2.32l8.5 4.84v9.68z"/><circle cx="12" cy="12" r="3"/></svg>
      </div>
      <div><div class="model-name">Llama 3.3 70B</div><div class="model-meta">Meta AI</div></div>
    </div>

    <div class="model-card" data-reveal>
      <div class="model-logo">
        <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 7v5l3 3" stroke="white" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
      </div>
      <div><div class="model-name">DeepSeek V3</div><div class="model-meta">DeepSeek</div></div>
    </div>

    <div class="model-card" data-reveal>
      <div class="model-logo">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-1 15v-3H8v-2h3V9l4 3-4 3z"/></svg>
      </div>
      <div><div class="model-name">o1 Preview</div><div class="model-meta">OpenAI · Reasoning</div></div>
    </div>

    <div class="model-card" data-reveal>
      <div class="model-logo">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M2 4v16h20V4H2zm18 14H4V8h16v10zm-2-2H6v-2h12v2zm0-4H6v-2h12v2z"/></svg>
      </div>
      <div><div class="model-name">Mistral Large</div><div class="model-meta">Mistral AI</div></div>
    </div>

    <div class="model-card" data-reveal>
      <div class="model-logo">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6z"/></svg>
      </div>
      <div><div class="model-name">90+ more</div><div class="model-meta">View catalog</div></div>
    </div>
  </div>
</section>


<!-- ===== CHAT HIGHLIGHT SECTION ===== -->
@php
  $__loc = app()->getLocale();
  $__chat = [
    'eyebrow'  => $__loc === 'ru' ? 'Новое' : ($__loc === 'en' ? 'New' : 'Yangi'),
    'subtitle' => $__loc === 'ru'
        ? '360+ AI моделей в одном месте — текст, код, генерация изображений, музыки и видео.'
        : ($__loc === 'en'
            ? '360+ AI models in one place — text, code, image, music and video generation.'
            : '360+ AI model bitta joyda — matn, kod, rasm, musiqa va video generatsiya.'),
    'cta'      => $__loc === 'ru' ? 'Открыть чат' : ($__loc === 'en' ? 'Open chat' : 'Chatni ochish'),
    'caps'     => $__loc === 'ru'
        ? ['Текст', 'Код', 'Изображения', 'Аудио', 'Видео']
        : ($__loc === 'en'
            ? ['Text', 'Code', 'Images', 'Audio', 'Video']
            : ['Matn', 'Kod', 'Rasm', 'Audio', 'Video']),
  ];
@endphp
<section class="section" id="chat">
  <div class="section-header">
    <div class="section-eyebrow">{{ $__chat['eyebrow'] }}</div>
    <h2 class="section-title">CloudAPI Chat</h2>
    <p class="section-subtitle">{{ $__chat['subtitle'] }}</p>
  </div>
  <div class="chat-promo" data-reveal>
    <div class="chat-promo-caps">
      @foreach(['forum' => $__chat['caps'][0], 'code' => $__chat['caps'][1], 'image' => $__chat['caps'][2], 'graphic_eq' => $__chat['caps'][3], 'movie' => $__chat['caps'][4]] as $__ic => $__lbl)
        <div class="chat-cap"><span class="material-icons-round">{{ $__ic }}</span><span>{{ $__lbl }}</span></div>
      @endforeach
    </div>
    <a href="{{ route('dashboard.chat.index') }}" class="btn btn-primary btn-lg">
      <span class="material-icons-round" style="font-size:18px;vertical-align:-4px;margin-right:6px">forum</span>{{ $__chat['cta'] }}
    </a>
  </div>
</section>
<style>
.chat-promo { max-width: 820px; margin: 0 auto; text-align: center; }
.chat-promo-caps { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; margin: 8px 0 28px; }
.chat-cap {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 12px 18px; border: 1px solid var(--border); border-radius: 12px;
  background: var(--bg-elevated); color: var(--text-strong); font-weight: 600; font-size: 14px;
}
.chat-cap .material-icons-round { font-size: 20px; color: var(--text-muted); }
</style>

<!-- ===== FEEDBACKS SECTION ===== -->
@php
    $__feedbacks = \App\Models\Feedback::forLanding(12);
    $__avgRating = $__feedbacks->avg('rating') ?? 5;
    $__totalCount = \App\Models\Feedback::where('is_published', 1)->whereNotNull('text')->where('text', '!=', '')->count();
@endphp

<section id="feedbacks" class="feedbacks-section">
  <div class="feedbacks-container">
    <div class="feedbacks-header">
      <div class="feedbacks-badge">
        <span class="material-icons-round">rate_review</span>
        Foydalanuvchilar fikri
      </div>
      <h2 class="feedbacks-title">Bizga ishonganlar so'zi</h2>
      <p class="feedbacks-subtitle">Real foydalanuvchilar tomonidan qoldirilgan haqiqiy fikrlar.</p>

      @if($__totalCount > 0)
      <div class="feedbacks-stats">
        <div class="feedbacks-stat">
          <div class="feedbacks-stat-value">
            {{ number_format($__avgRating, 1) }}
            <span class="material-icons-round" style="color:#FBBF24;font-size:24px;vertical-align:-4px">star</span>
          </div>
          <div class="feedbacks-stat-label">O'rtacha baho</div>
        </div>
        <div class="feedbacks-stat">
          <div class="feedbacks-stat-value">{{ $__totalCount }}+</div>
          <div class="feedbacks-stat-label">Feedback</div>
        </div>
      </div>
      @endif
    </div>

    @if($__feedbacks->count() > 0)
    <div class="feedbacks-grid">
      @foreach($__feedbacks as $fb)
      <div class="feedback-card">
        <div class="feedback-header">
          <div class="feedback-avatar" style="background: {{ $fb->avatar_color }}">{{ $fb->initial }}</div>
          <div class="feedback-user">
            <div class="feedback-name">{{ $fb->display_name }}</div>
            <div class="feedback-date">{{ $fb->created_at->format('M Y') }}</div>
          </div>
          <div class="feedback-rating">
            @for($i = 1; $i <= 5; $i++)
              <span class="material-icons-round" style="color: {{ $i <= $fb->rating ? '#FBBF24' : 'var(--border)' }}; font-size: 15px">star</span>
            @endfor
          </div>
        </div>
        <div class="feedback-text">{{ $fb->text }}</div>
        @if($fb->admin_reply)
        <div class="feedback-reply">
          <div class="feedback-reply-header">
            <span class="material-icons-round">reply</span>
            CloudAPI jamoasidan javob
          </div>
          <div class="feedback-reply-text">{{ Str::limit($fb->admin_reply, 200) }}</div>
        </div>
        @endif
      </div>
      @endforeach
    </div>
    @endif

    <div class="feedback-cta">
      <h3 class="feedback-cta-title">Fikringiz biz uchun muhim</h3>
      <p class="feedback-cta-subtitle">Platforma haqidagi taassurotlaringizni yozing yoki Telegram bot orqali murojaat qiling.</p>
      <div class="feedback-cta-buttons">
        <button class="fb-btn fb-btn-primary" onclick="openFeedbackModal()">
          <span class="material-icons-round">edit</span>
          Fikr yozish
        </button>
        <a href="https://t.me/cloudapiuzbot" target="_blank" class="fb-btn fb-btn-secondary">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="#229ED9">
            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.643.135-.953l11.566-4.458c.538-.196 1.006.128.832.94z"/>
          </svg>
          Telegram orqali
        </a>
      </div>
    </div>
  </div>
</section>

<!-- FEEDBACK MODAL -->
<div class="fb-modal" id="feedbackModal" onclick="if(event.target === this) closeFeedbackModal()">
  <div class="fb-modal-content">
    <button class="fb-modal-close" onclick="closeFeedbackModal()">
      <span class="material-icons-round">close</span>
    </button>
    <div class="fb-modal-header">
      <h3>Fikr yozing</h3>
      <p>Sizning fikringiz platforma yaxshilanishiga yordam beradi</p>
    </div>
    <form id="feedbackForm" onsubmit="return submitFeedback(event)">
      @csrf
      @guest
      <div class="fb-field">
        <label>Ismingiz</label>
        <input type="text" name="name" maxlength="100" placeholder="Ismingiz" class="fb-input">
      </div>
      @endguest
      <div class="fb-field">
        <label>Baho</label>
        <div class="rating-input">
          @for($i = 1; $i <= 5; $i++)
          <button type="button" class="rating-star{{ $i <= 5 ? ' active' : '' }}" data-value="{{ $i }}" onclick="setRating({{ $i }})">
            <span class="material-icons-round">star</span>
          </button>
          @endfor
          <input type="hidden" name="rating" id="ratingValue" value="5">
        </div>
      </div>
      <div class="fb-field">
        <label>Fikringiz</label>
        <textarea name="text" required minlength="5" maxlength="1000" rows="4" placeholder="Platforma haqida nima o'ylaysiz?" class="fb-textarea"></textarea>
        <div class="fb-char-count"><span id="charCount">0</span>/1000</div>
      </div>
      <div class="fb-form-message" id="feedbackMessage"></div>
      <button type="submit" class="fb-btn fb-btn-primary fb-btn-full" id="feedbackSubmitBtn">
        <span class="material-icons-round">send</span>
        Yuborish
      </button>
    </form>
  </div>
</div>


<!-- CTA -->
<section class="cta-section">
  <h2>{{ __('landing.cta.title') }}</h2>
  <p>{{ __('landing.cta.subtitle') }}</p>
  <div class="hero-cta" style="margin-top:36px">
    <a href="{{ route('register') }}" class="btn btn-lg" style="background:white;color:#0a0a0a">
      {{ __('landing.cta.button') }}
      <span class="material-icons-round">arrow_forward</span>
    </a>
    <a href="{{ route('docs') }}" class="btn btn-lg" style="background:transparent;color:white;border:1px solid rgba(255,255,255,.2)">
      <span class="material-icons-round">code</span>
      {{ __('landing.hero.cta_secondary') }}
    </a>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-inner">
    <div>
      <div class="brand">
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
      </div>
      <p class="footer-brand">{{ __('landing.footer.tagline') }}</p>
    </div>
    <div class="footer-col">
      <h4>{{ __('landing.footer.product') }}</h4>
      <a href="#features">{{ __('landing.nav.features') }}</a>
      <a href="#models">{{ __('landing.nav.models') }}</a>
      <a href="{{ route('pricing') }}">{{ __('landing.nav.pricing') }}</a>
      <a href="{{ route('docs') }}">{{ __('landing.nav.docs') }}</a>
    </div>
    <div class="footer-col">
      <h4>{{ __('landing.footer.company') }}</h4>
      <a href="#about">{{ __('landing.footer.about') }}</a>
      <a href="#">{{ __('landing.footer.blog') }}</a>
      <a href="#">{{ __('landing.footer.contact') }}</a>
      <a href="#">{{ __('landing.footer.careers') }}</a>
    </div>
    <div class="footer-col">
      <h4>{{ __('landing.footer.legal') }}</h4>
      <a href="{{ route('privacy') }}">{{ __('landing.footer.privacy') }}</a>
      <a href="{{ route('terms') }}">{{ __('landing.footer.terms') }}</a>
      <a href="{{ route('security') }}">{{ __('landing.footer.security') }}</a>
      <a href="#">{{ __('landing.footer.status') }}</a>
    </div>
  </div>
  <div class="footer-bottom">
    <div>© {{ date('Y') }} CloudAPI. {{ __('landing.footer.rights') }}</div>
    <div>{{ __('landing.footer.built_in') }}</div>
  </div>
</footer>

@endsection

@push('scripts')
<script>
// ===== GLOBAL 3D WIRES across entire viewport =====
(function() {
  const canvas = document.getElementById('wireCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, dpr = window.devicePixelRatio || 1;
  let wires = [];
  let mouse = { x: -1000, y: -1000, active: false };

  function resize() {
    W = window.innerWidth;
    H = window.innerHeight;
    canvas.width = W * dpr;
    canvas.height = H * dpr;
    canvas.style.width = W + 'px';
    canvas.style.height = H + 'px';
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(dpr, dpr);
    createWires();
  }

  function createWires() {
    wires = [];
    const count = 24;
    for (let i = 0; i < count; i++) {
      // Edges or corners
      const side = Math.floor(Math.random() * 4);
      let sx, sy, ex, ey;

      if (side === 0) { sx = 0; sy = Math.random() * H; }
      else if (side === 1) { sx = W; sy = Math.random() * H; }
      else if (side === 2) { sx = Math.random() * W; sy = 0; }
      else { sx = Math.random() * W; sy = H; }

      // End point — somewhere in viewport
      ex = W * (0.2 + Math.random() * 0.6);
      ey = H * (0.2 + Math.random() * 0.6);

      const dx = ex - sx, dy = ey - sy;
      const dist = Math.sqrt(dx*dx + dy*dy);
      const perpX = -dy / dist;
      const perpY = dx / dist;
      const curvature = (Math.random() - 0.5) * dist * 0.4;

      const ctrl1X = sx + dx * 0.33 + perpX * curvature;
      const ctrl1Y = sy + dy * 0.33 + perpY * curvature;
      const ctrl2X = sx + dx * 0.66 + perpX * curvature;
      const ctrl2Y = sy + dy * 0.66 + perpY * curvature;

      wires.push({
        sx, sy, ex, ey,
        ctrl1X, ctrl1Y, ctrl2X, ctrl2Y,
        baseCtrl1X: ctrl1X, baseCtrl1Y: ctrl1Y,
        baseCtrl2X: ctrl2X, baseCtrl2Y: ctrl2Y,
        opacity: 0.2 + Math.random() * 0.4,
        width: 0.5 + Math.random() * 0.8,
        pulse: Math.random() * Math.PI * 2,
        pulseSpeed: 0.015 + Math.random() * 0.02,
      });
    }
  }

  function isDark() { return document.documentElement.dataset.theme === 'dark'; }

  function draw() {
    ctx.clearRect(0, 0, W, H);
    const dark = isDark();

    wires.forEach(w => {
      if (mouse.active) {
        const dx1 = mouse.x - w.baseCtrl1X;
        const dy1 = mouse.y - w.baseCtrl1Y;
        const dist1 = Math.sqrt(dx1 * dx1 + dy1 * dy1);
        const influence1 = Math.max(0, 250 - dist1) / 250;
        w.ctrl1X += ((w.baseCtrl1X + dx1 * influence1 * 0.4) - w.ctrl1X) * 0.15;
        w.ctrl1Y += ((w.baseCtrl1Y + dy1 * influence1 * 0.4) - w.ctrl1Y) * 0.15;

        const dx2 = mouse.x - w.baseCtrl2X;
        const dy2 = mouse.y - w.baseCtrl2Y;
        const dist2 = Math.sqrt(dx2 * dx2 + dy2 * dy2);
        const influence2 = Math.max(0, 250 - dist2) / 250;
        w.ctrl2X += ((w.baseCtrl2X + dx2 * influence2 * 0.4) - w.ctrl2X) * 0.15;
        w.ctrl2Y += ((w.baseCtrl2Y + dy2 * influence2 * 0.4) - w.ctrl2Y) * 0.15;
      } else {
        w.ctrl1X += (w.baseCtrl1X - w.ctrl1X) * 0.05;
        w.ctrl1Y += (w.baseCtrl1Y - w.ctrl1Y) * 0.05;
        w.ctrl2X += (w.baseCtrl2X - w.ctrl2X) * 0.05;
        w.ctrl2Y += (w.baseCtrl2Y - w.ctrl2Y) * 0.05;
      }

      w.pulse += w.pulseSpeed;
      const pulseOpacity = (Math.sin(w.pulse) + 1) / 2 * 0.5 + 0.5;

      const color = dark ? `rgba(156, 163, 175, ${w.opacity * pulseOpacity})` : `rgba(107, 114, 128, ${w.opacity * pulseOpacity})`;
      ctx.strokeStyle = color;
      ctx.lineWidth = w.width;

      ctx.beginPath();
      ctx.moveTo(w.sx, w.sy);
      ctx.bezierCurveTo(w.ctrl1X, w.ctrl1Y, w.ctrl2X, w.ctrl2Y, w.ex, w.ey);
      ctx.stroke();

      // Glowing dot at end
      const dotGrad = ctx.createRadialGradient(w.ex, w.ey, 0, w.ex, w.ey, 8);
      dotGrad.addColorStop(0, dark ? `rgba(255,255,255,${pulseOpacity * 0.8})` : `rgba(17,17,17,${pulseOpacity * 0.8})`);
      dotGrad.addColorStop(1, 'rgba(255,255,255,0)');
      ctx.fillStyle = dotGrad;
      ctx.beginPath();
      ctx.arc(w.ex, w.ey, 8, 0, Math.PI * 2);
      ctx.fill();
    });

    requestAnimationFrame(draw);
  }

  resize();
  draw();

  window.addEventListener('resize', resize);

  document.addEventListener('mousemove', (e) => {
    mouse.x = e.clientX;
    mouse.y = e.clientY;
    mouse.active = true;
  });

  document.addEventListener('mouseleave', () => { mouse.active = false; });
})();

// ===== GLOBAL INTERACTIVE NETWORK across entire viewport =====
(function() {
  const canvas = document.getElementById('netCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, nodes = [], mouse = { x: -1000, y: -1000, active: false };
  const NODE_COUNT = 200;
  const CONNECT_DIST = 100;
  const dpr = window.devicePixelRatio || 1;

  function resize() {
    W = window.innerWidth;
    H = window.innerHeight;
    canvas.width = W * dpr;
    canvas.height = H * dpr;
    canvas.style.width = W + 'px';
    canvas.style.height = H + 'px';
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(dpr, dpr);
    createNodes();
  }

  function createNodes() {
    nodes = [];
    for (let i = 0; i < NODE_COUNT; i++) {
      nodes.push({
        x: Math.random() * W,
        y: Math.random() * H,
        vx: (Math.random() - .5) * .25,
        vy: (Math.random() - .5) * .25,
        ox: 0, oy: 0,
        size: Math.random() * 1.5 + 1.2,
        hub: Math.random() > 0.85,
      });
    }
  }

  function isDark() { return document.documentElement.dataset.theme === 'dark'; }

  function draw() {
    ctx.clearRect(0, 0, W, H);
    const dark = isDark();
    const lineColor = dark ? '156, 163, 175' : '107, 114, 128';
    const dotColor = dark ? '255, 255, 255' : '17, 17, 17';

    nodes.forEach(n => {
      n.x += n.vx;
      n.y += n.vy;
      if (n.x < 0 || n.x > W) n.vx *= -1;
      if (n.y < 0 || n.y > H) n.vy *= -1;
      n.x = Math.max(0, Math.min(W, n.x));
      n.y = Math.max(0, Math.min(H, n.y));

      const dx = n.x - mouse.x;
      const dy = n.y - mouse.y;
      const dist = Math.sqrt(dx * dx + dy * dy);
      if (mouse.active && dist < 150) {
        const force = (150 - dist) / 150;
        n.ox += (dx / dist) * force * 3;
        n.oy += (dy / dist) * force * 3;
      }
      n.ox *= 0.9;
      n.oy *= 0.9;
    });

    for (let i = 0; i < nodes.length; i++) {
      for (let j = i + 1; j < nodes.length; j++) {
        const a = nodes[i], b = nodes[j];
        const dx = (a.x + a.ox) - (b.x + b.ox);
        const dy = (a.y + a.oy) - (b.y + b.oy);
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < CONNECT_DIST) {
          const opacity = (1 - dist / CONNECT_DIST) * 0.35;
          ctx.strokeStyle = `rgba(${lineColor}, ${opacity})`;
          ctx.lineWidth = a.hub || b.hub ? 0.8 : 0.5;
          ctx.beginPath();
          ctx.moveTo(a.x + a.ox, a.y + a.oy);
          ctx.lineTo(b.x + b.ox, b.y + b.oy);
          ctx.stroke();
        }
      }
    }

    nodes.forEach(n => {
      const x = n.x + n.ox, y = n.y + n.oy;
      const size = n.hub ? n.size * 1.5 : n.size;

      if (n.hub) {
        const grad = ctx.createRadialGradient(x, y, 0, x, y, size * 3);
        grad.addColorStop(0, `rgba(${dotColor}, 0.4)`);
        grad.addColorStop(1, `rgba(${dotColor}, 0)`);
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.arc(x, y, size * 3, 0, Math.PI * 2);
        ctx.fill();
      }

      ctx.fillStyle = `rgba(${dotColor}, ${n.hub ? 1 : 0.7})`;
      ctx.beginPath();
      ctx.arc(x, y, size, 0, Math.PI * 2);
      ctx.fill();
    });

    requestAnimationFrame(draw);
  }

  resize();
  draw();

  window.addEventListener('resize', resize);

  document.addEventListener('mousemove', (e) => {
    mouse.x = e.clientX;
    mouse.y = e.clientY;
    mouse.active = true;
  });

  document.addEventListener('mouseleave', () => { mouse.active = false; });
})();

// ===== CODE TABS =====
(function() {
  const codes = {
    curl: `<span class="com"># OpenAI-compatible API</span>
curl https://api.cloudapi.uz/v1/chat/completions \\
  -H <span class="str">"Authorization: Bearer cap-..."</span> \\
  -H <span class="str">"Content-Type: application/json"</span> \\
  -d <span class="str">'{
    "model": "anthropic/claude-3.5-sonnet",
    "messages": [{"role": "user", "content": "Hello"}]
  }'</span>`,
    python: `<span class="kw">from</span> openai <span class="kw">import</span> OpenAI

client = <span class="fn">OpenAI</span>(
    api_key=<span class="str">"cap-..."</span>,
    base_url=<span class="str">"https://api.cloudapi.uz/v1"</span>
)

response = client.chat.completions.<span class="fn">create</span>(
    model=<span class="str">"anthropic/claude-3.5-sonnet"</span>,
    messages=[{<span class="str">"role"</span>: <span class="str">"user"</span>, <span class="str">"content"</span>: <span class="str">"Salom"</span>}]
)

<span class="fn">print</span>(response.choices[<span class="var">0</span>].message.content)`,
    node: `<span class="kw">import</span> OpenAI <span class="kw">from</span> <span class="str">'openai'</span>;

<span class="kw">const</span> client = <span class="kw">new</span> <span class="fn">OpenAI</span>({
  apiKey: <span class="str">'cap-...'</span>,
  baseURL: <span class="str">'https://api.cloudapi.uz/v1'</span>,
});

<span class="kw">const</span> response = <span class="kw">await</span> client.chat.completions.<span class="fn">create</span>({
  model: <span class="str">'anthropic/claude-3.5-sonnet'</span>,
  messages: [{ role: <span class="str">'user'</span>, content: <span class="str">'Salom'</span> }],
});`,
  };

  const block = document.getElementById('codeBlock');
  const tabs = document.querySelectorAll('.code-tab');

  function setLang(lang) {
    tabs.forEach(t => t.classList.toggle('active', t.dataset.lang === lang));
    block.innerHTML = codes[lang];
  }

  tabs.forEach(t => t.addEventListener('click', () => setLang(t.dataset.lang)));
  setLang('curl');
})();

// ===== SCROLL REVEAL =====
(function() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => entry.target.classList.add('in-view'), i * 60);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('[data-reveal]').forEach(el => observer.observe(el));
})();

// ===== MOBILE MENU =====
function openMobileMenu() {
  document.getElementById('mobileMenu').classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closeMobileMenu() {
  document.getElementById('mobileMenu').classList.remove('active');
  document.body.style.overflow = '';
}

// ===== FEEDBACK MODAL =====
let currentRating = 5;
function openFeedbackModal() {
  document.getElementById('feedbackModal').classList.add('active');
  document.body.style.overflow = 'hidden';
  setRating(5);
}
function closeFeedbackModal() {
  document.getElementById('feedbackModal').classList.remove('active');
  document.body.style.overflow = '';
}
function setRating(value) {
  currentRating = value;
  document.getElementById('ratingValue').value = value;
  document.querySelectorAll('.rating-star').forEach(star => {
    const starValue = parseInt(star.dataset.value);
    star.classList.toggle('active', starValue <= value);
  });
}

document.addEventListener('DOMContentLoaded', function() {
  const textarea = document.querySelector('#feedbackForm textarea[name="text"]');
  const counter = document.getElementById('charCount');
  if (textarea && counter) {
    textarea.addEventListener('input', function() {
      counter.textContent = this.value.length;
    });
  }
});

async function submitFeedback(e) {
  e.preventDefault();
  const form = e.target;
  const btn = document.getElementById('feedbackSubmitBtn');
  const msg = document.getElementById('feedbackMessage');

  msg.classList.remove('show', 'success', 'error');
  btn.disabled = true;
  const originalHtml = btn.innerHTML;
  btn.innerHTML = '<span class="material-icons-round" style="animation:spinAnim 1s linear infinite">refresh</span> Yuborilmoqda';

  const formData = new FormData(form);

  try {
    const res = await fetch('{{ route("feedback.store") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: formData
    });

    const data = await res.json();

    if (!res.ok || !data.ok) {
      const errors = data.errors ? Object.values(data.errors).flat().join(', ') : data.message;
      throw new Error(errors || 'Xato yuz berdi');
    }

    msg.textContent = data.message;
    msg.classList.add('show', 'success');
    form.reset();
    setRating(5);

    setTimeout(() => {
      closeFeedbackModal();
      window.location.reload();
    }, 1800);

  } catch (err) {
    msg.textContent = err.message;
    msg.classList.add('show', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }

  return false;
}

</script>
@endpush