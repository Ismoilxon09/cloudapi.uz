@extends('layouts.app')

@section('title', 'AI Town — CloudAPI')

@push('styles')
<style>
.town-wrap { position: relative; height: calc(100vh - 60px); overflow: hidden;
  background: radial-gradient(130% 100% at 50% 30%, #221a4d 0%, #17143a 55%, #0f0d29 100%); }
#townCanvas { position: absolute; inset: 0; width: 100%; height: 100%; display: block; }
.town-hud { position: absolute; z-index: 5; pointer-events: none; font-family: 'Inter', system-ui, sans-serif; color: #f5efe4; }
.town-tl { top: 18px; left: 18px; display: flex; flex-direction: column; gap: 13px; }
.town-title { display: flex; align-items: center; gap: 9px; font-weight: 800; font-size: 17px; letter-spacing: -0.02em; text-shadow: 0 2px 10px rgba(0,0,0,.5); }
.town-title .material-icons-round { font-size: 20px; color: #ffcf8a; }
.town-stats { display: flex; gap: 9px; flex-wrap: wrap; max-width: 340px; }
.town-stat { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.12); backdrop-filter: blur(10px); border-radius: 13px; padding: 10px 13px; min-width: 86px; }
.town-stat .v { font-family: 'JetBrains Mono', monospace; font-size: 20px; font-weight: 600; line-height: 1; font-variant-numeric: tabular-nums; }
.town-stat .k { font-size: 9.5px; color: rgba(245,239,228,.6); text-transform: uppercase; letter-spacing: .09em; margin-top: 6px; }
.town-tr { top: 18px; right: 18px; display: flex; align-items: center; gap: 10px; pointer-events: auto; }
.town-chip { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.12); backdrop-filter: blur(10px); border-radius: 99px; padding: 8px 14px; font-size: 12px; font-weight: 600; color: rgba(245,239,228,.7); }
.town-chip .dot { width: 8px; height: 8px; border-radius: 50%; background: #6ee7a8; box-shadow: 0 0 10px #6ee7a8; animation: townblink 1.7s infinite; }
@keyframes townblink { 0%,100%{opacity:1;} 50%{opacity:.35;} }
.town-clock { font-family: 'JetBrains Mono', monospace; color: #f5efe4; font-variant-numeric: tabular-nums; }
.town-back { display: inline-flex; align-items: center; gap: 6px; color: #f5efe4; font-size: 13px; font-weight: 600; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14); border-radius: 10px; padding: 8px 13px; backdrop-filter: blur(10px); transition: background .15s; }
.town-back:hover { background: rgba(255,255,255,.16); }
.town-legend { position: absolute; bottom: 18px; left: 18px; z-index: 5; display: flex; gap: 14px; flex-wrap: wrap; max-width: 62%; font-family: 'Inter', system-ui, sans-serif; }
.town-leg { display: inline-flex; align-items: center; gap: 7px; font-size: 11.5px; color: rgba(245,239,228,.6); }
.town-leg i { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
.town-empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 4; color: rgba(245,239,228,.6); font-size: 14px; text-align: center; padding: 24px; font-family: 'Inter', system-ui, sans-serif; }
.town-empty a { color: #ffcf8a; text-decoration: underline; }
@media (max-width: 720px) { .town-legend { display: none; } }
</style>
@endpush

@section('content')
<div class="town-wrap">
  <canvas id="townCanvas"></canvas>

  <div class="town-hud town-tl">
    <div class="town-title"><span class="material-icons-round">hub</span> AI Town</div>
    <div class="town-stats">
      <div class="town-stat"><div class="v" data-hud="requests">0</div><div class="k">So'rov · bugun</div></div>
      <div class="town-stat"><div class="v" data-hud="tokens">0</div><div class="k">Token</div></div>
      <div class="town-stat"><div class="v" data-hud="cost">0</div><div class="k">So'm</div></div>
      <div class="town-stat"><div class="v" data-hud="busy">0</div><div class="k">Ishda</div></div>
    </div>
  </div>

  <div class="town-hud town-tr">
    <span class="town-chip"><span class="town-clock" id="townClock">00:00:00</span></span>
    <span class="town-chip"><span class="dot"></span> Jonli</span>
    <a href="{{ route('vantage.index') }}" class="town-back"><span class="material-icons-round" style="font-size:16px;">arrow_back</span> Vantage</a>
  </div>

  <div class="town-hud town-legend" id="townLegend"></div>

  @if($agents->isEmpty())
    <div class="town-empty">Qishloq hali bo'sh — <a href="{{ route('agents.index') }}">agent yarating</a>, u shu yerda personaj bo'lib jonlaydi.</div>
  @endif
</div>
@endsection

@push('scripts')
<script>
@php
  $roleMap = ['coder'=>'coder','support'=>'support','sales'=>'sales','tutor'=>'tutor','general'=>'general','custom'=>'general'];
  $townAgents = $agents->map(fn($a) => ['name' => $a->name, 'role' => $roleMap[$a->behavior_preset] ?? 'general'])->values();
@endphp
window.TOWN = {!! json_encode([
  'agents'    => $townAgents,
  'kpis'      => $kpis,
  'recent'    => $recent,
  'streamUrl' => route('vantage.stream'),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};

(function () {
  var T = window.TOWN;
  if (!T.agents.length) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var FONT = "'Inter', system-ui, sans-serif";

  var ROLES = {
    coder:   { color: '#7db4ff', label: 'Dasturchi' },
    support: { color: '#6ee7a8', label: "Qo'llab-quvvatlash" },
    sales:   { color: '#ffbf5c', label: 'Sotuv' },
    tutor:   { color: '#c79bff', label: "O'qituvchi" },
    general: { color: '#ff9ec4', label: 'Umumiy' }
  };

  var canvas = document.getElementById('townCanvas'), ctx = canvas.getContext('2d');
  var W = 0, H = 0, DPR = Math.min(window.devicePixelRatio || 1, 2), cx = 0, cy = 0, plazaR = 0;
  var chars = [];
  var lastTs = 0; (T.recent || []).forEach(function (e) { if (e.ts > lastTs) lastTs = e.ts; });

  function rand(a, b) { return a + Math.random() * (b - a); }
  function dist(ax, ay, bx, by) { return Math.hypot(ax - bx, ay - by); }
  function shade(hex, amt) { var n = parseInt(hex.slice(1), 16); var r = Math.max(0, Math.min(255, (n >> 16) + amt)), g = Math.max(0, Math.min(255, ((n >> 8) & 255) + amt)), b = Math.max(0, Math.min(255, (n & 255) + amt)); return 'rgb(' + r + ',' + g + ',' + b + ')'; }

  T.agents.forEach(function (a) {
    var role = ROLES[a.role] ? a.role : 'general';
    chars.push({ name: a.name, role: role, color: ROLES[role].color, state: 'idle', timer: rand(10, 90), face: 1, bob: Math.random() * 6.28, speed: rand(0.5, 0.75) });
  });

  function layout() {
    W = canvas.clientWidth; H = canvas.clientHeight;
    canvas.width = W * DPR; canvas.height = H * DPR; ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
    cx = W / 2; cy = H / 2 + 8; plazaR = Math.max(140, Math.min(W, H) * 0.36);
    var n = chars.length;
    chars.forEach(function (ch, i) {
      var a = (i / n) * Math.PI * 2 - Math.PI / 2;
      ch.home = { x: cx + Math.cos(a) * plazaR, y: cy + Math.sin(a) * plazaR };
      ch.work = { x: cx + Math.cos(a) * (plazaR * 0.34), y: cy + Math.sin(a) * (plazaR * 0.34) };
      if (ch.x === undefined) { ch.x = ch.home.x; ch.y = ch.home.y; ch.tx = ch.home.x; ch.ty = ch.home.y; }
    });
  }
  window.addEventListener('resize', layout);

  var lanterns = []; for (var i = 0; i < 20; i++) lanterns.push({ x: Math.random(), y: Math.random(), p: Math.random() * 6.28, r: rand(1, 2.4) });
  var stars = []; for (var s = 0; s < 60; s++) stars.push({ x: Math.random(), y: Math.random(), r: Math.random() * 1.2 + 0.2, a: Math.random() * 0.4 + 0.08 });

  function triggerWork() {
    var idle = chars.filter(function (c) { return c.state === 'idle'; });
    var pool = idle.length ? idle : chars;
    var ch = pool[Math.floor(Math.random() * pool.length)];
    if (ch.state === 'idle') { ch.state = 'toWork'; ch.tx = ch.work.x; ch.ty = ch.work.y; }
  }

  function stepChar(ch, dt, time) {
    var d = dist(ch.x, ch.y, ch.tx, ch.ty), moving = d > 2;
    if (moving) { var sp = ch.speed * dt; ch.x += (ch.tx - ch.x) / d * Math.min(sp, d); ch.y += (ch.ty - ch.y) / d * Math.min(sp, d); ch.face = (ch.tx >= ch.x) ? 1 : -1; ch.bob += 0.25 * dt; }
    switch (ch.state) {
      case 'idle': if (!moving) { ch.timer -= dt; if (ch.timer <= 0) { var a = Math.random() * 6.28;
        ch.tx = Math.min(Math.max(cx + Math.cos(a) * (plazaR * 0.62 + rand(-30, 30)), 40), W - 40);
        ch.ty = Math.min(Math.max(cy + Math.sin(a) * (plazaR * 0.62 + rand(-30, 30)), 60), H - 40); ch.timer = rand(30, 160); } } break;
      case 'toWork': if (!moving) { ch.state = 'think'; ch.timer = rand(60, 100); } break;
      case 'think': ch.timer -= dt; if (ch.timer <= 0) { ch.state = 'reply'; ch.timer = rand(55, 90); } break;
      case 'reply': ch.timer -= dt; if (ch.timer <= 0) { ch.state = 'return'; ch.tx = ch.home.x; ch.ty = ch.home.y; } break;
      case 'return': if (!moving) { ch.state = 'idle'; ch.timer = rand(20, 90); } break;
    }
  }

  function roundRect(x, y, w, h, r) { ctx.beginPath(); ctx.moveTo(x + r, y); ctx.arcTo(x + w, y, x + w, y + h, r); ctx.arcTo(x + w, y + h, x, y + h, r); ctx.arcTo(x, y + h, x, y, r); ctx.arcTo(x, y, x + w, y, r); ctx.closePath(); }

  function drawGround(time) {
    var g = ctx.createRadialGradient(cx, cy, 0, cx, cy, plazaR + 60);
    g.addColorStop(0, 'rgba(70,58,130,.55)'); g.addColorStop(0.7, 'rgba(43,35,88,.25)'); g.addColorStop(1, 'rgba(43,35,88,0)');
    ctx.beginPath(); ctx.arc(cx, cy, plazaR + 60, 0, 6.2832); ctx.fillStyle = g; ctx.fill();
    ctx.beginPath(); ctx.arc(cx, cy, plazaR, 0, 6.2832); ctx.strokeStyle = 'rgba(255,255,255,.06)'; ctx.lineWidth = 1; ctx.stroke();
    for (var i = 0; i < stars.length; i++) { var st = stars[i]; ctx.beginPath(); ctx.arc(st.x * W, st.y * H, st.r, 0, 6.2832); ctx.fillStyle = 'rgba(245,239,228,' + st.a + ')'; ctx.fill(); }
    for (var j = 0; j < lanterns.length; j++) { var l = lanterns[j], fl = 0.5 + Math.sin(time / 500 + l.p) * 0.3, lx = l.x * W, ly = l.y * H;
      var lg = ctx.createRadialGradient(lx, ly, 0, lx, ly, 14); lg.addColorStop(0, 'rgba(255,179,92,' + (0.5 * fl) + ')'); lg.addColorStop(1, 'rgba(255,179,92,0)');
      ctx.beginPath(); ctx.arc(lx, ly, 14, 0, 6.2832); ctx.fillStyle = lg; ctx.fill();
      ctx.beginPath(); ctx.arc(lx, ly, l.r, 0, 6.2832); ctx.fillStyle = 'rgba(255,207,138,' + (0.7 * fl + 0.2) + ')'; ctx.fill(); }
  }

  function drawTower(time) {
    chars.forEach(function (ch) { ctx.beginPath(); ctx.moveTo(cx, cy); ctx.lineTo(ch.home.x, ch.home.y); ctx.strokeStyle = 'rgba(255,207,138,.05)'; ctx.lineWidth = 6; ctx.stroke(); });
    var g = ctx.createRadialGradient(cx, cy, 0, cx, cy, 46); g.addColorStop(0, 'rgba(255,207,138,.5)'); g.addColorStop(1, 'rgba(255,207,138,0)');
    ctx.beginPath(); ctx.arc(cx, cy, 46, 0, 6.2832); ctx.fillStyle = g; ctx.fill();
    ctx.beginPath(); ctx.arc(cx, cy, 17, 0, 6.2832); ctx.fillStyle = '#3a2f66'; ctx.fill();
    ctx.beginPath(); ctx.arc(cx, cy, 17, 0, 6.2832); ctx.strokeStyle = 'rgba(255,207,138,.5)'; ctx.lineWidth = 2; ctx.stroke();
    for (var w = 0; w < 4; w++) { var wa = time / 900 + w * 1.57, flick = 0.6 + Math.sin(time / 200 + w * 2) * 0.3, wx = cx + Math.cos(wa) * 8, wy = cy + Math.sin(wa) * 8;
      ctx.beginPath(); ctx.arc(wx, wy, 2.4, 0, 6.2832); ctx.fillStyle = 'rgba(255,220,160,' + flick + ')'; ctx.fill(); }
    ctx.fillStyle = 'rgba(245,239,228,.55)'; ctx.font = '600 11px ' + FONT; ctx.textAlign = 'center'; ctx.fillText('CloudAPI', cx, cy + 34);
  }

  function accessory(ch, x, y) {
    if (ch.role === 'support') { ctx.strokeStyle = '#2b2358'; ctx.lineWidth = 1.4; ctx.beginPath(); ctx.arc(x, y - 8, 7, Math.PI * 1.1, Math.PI * 1.9); ctx.stroke(); ctx.beginPath(); ctx.arc(x - 6, y - 7, 1.6, 0, 6.2832); ctx.fillStyle = '#2b2358'; ctx.fill(); }
    else if (ch.role === 'coder') { roundRect(x - 5, y + 2, 10, 6, 1.5); ctx.fillStyle = '#2b2358'; ctx.fill(); ctx.fillStyle = shade(ch.color, 20); ctx.fillRect(x - 4, y + 3, 8, 3); }
    else if (ch.role === 'sales') { roundRect(x + 5, y + 1, 6, 5, 1); ctx.fillStyle = '#2b2358'; ctx.fill(); ctx.fillStyle = shade(ch.color, 15); ctx.fillRect(x + 6.5, y + 0.5, 3, 1.2); }
    else if (ch.role === 'tutor') { roundRect(x - 5, y + 2, 10, 6, 1); ctx.fillStyle = shade(ch.color, 12); ctx.fill(); ctx.strokeStyle = 'rgba(255,255,255,.5)'; ctx.lineWidth = 0.8; ctx.beginPath(); ctx.moveTo(x, y + 2); ctx.lineTo(x, y + 8); ctx.stroke(); }
    else { ctx.fillStyle = '#fff6df'; ctx.beginPath(); ctx.arc(x + 6, y - 12, 1.4, 0, 6.2832); ctx.fill(); }
  }
  function thinkBubble(x, y, time) {
    roundRect(x - 12, y - 8, 24, 14, 7); ctx.fillStyle = 'rgba(245,239,228,.92)'; ctx.fill();
    ctx.beginPath(); ctx.arc(x - 2, y + 8, 1.6, 0, 6.2832); ctx.fillStyle = 'rgba(245,239,228,.92)'; ctx.fill();
    for (var i = 0; i < 3; i++) { var a = 0.35 + Math.abs(Math.sin(time / 220 + i * 0.9)) * 0.6; ctx.beginPath(); ctx.arc(x - 6 + i * 6, y - 1, 1.8, 0, 6.2832); ctx.fillStyle = 'rgba(43,35,88,' + a + ')'; ctx.fill(); }
  }
  function replyBubble(x, y, color) {
    roundRect(x - 15, y - 9, 30, 16, 7); ctx.fillStyle = color; ctx.fill();
    ctx.beginPath(); ctx.moveTo(x - 3, y + 6); ctx.lineTo(x + 2, y + 11); ctx.lineTo(x + 3, y + 6); ctx.closePath(); ctx.fillStyle = color; ctx.fill();
    ctx.strokeStyle = 'rgba(15,13,41,.65)'; ctx.lineWidth = 1.4; ctx.lineCap = 'round';
    for (var i = 0; i < 2; i++) { ctx.beginPath(); ctx.moveTo(x - 9, y - 3 + i * 5); ctx.lineTo(x + (i ? 4 : 9), y - 3 + i * 5); ctx.stroke(); }
  }

  function drawChar(ch, time) {
    var moving = dist(ch.x, ch.y, ch.tx, ch.ty) > 2;
    var bob = (ch.state !== 'idle' || moving) ? Math.abs(Math.sin(ch.bob)) * 2 : Math.sin(time / 700 + ch.bob) * 0.8;
    var x = ch.x, y = ch.y - bob;
    ctx.beginPath(); ctx.ellipse(ch.x, ch.y + 9, 8, 3, 0, 0, 6.2832); ctx.fillStyle = 'rgba(0,0,0,.28)'; ctx.fill();
    var grd = ctx.createLinearGradient(x, y - 6, x, y + 10); grd.addColorStop(0, ch.color); grd.addColorStop(1, shade(ch.color, -18));
    roundRect(x - 7, y - 4, 14, 15, 6); ctx.fillStyle = grd; ctx.fill();
    ctx.beginPath(); ctx.arc(x, y - 8, 6, 0, 6.2832); ctx.fillStyle = '#f6e6d2'; ctx.fill();
    ctx.beginPath(); ctx.arc(x, y - 9.5, 6, Math.PI, 0); ctx.fillStyle = shade(ch.color, 8); ctx.fill();
    ctx.fillStyle = '#2b2358'; var ex = ch.face * 1.6;
    ctx.beginPath(); ctx.arc(x - 2 + ex, y - 8, 1, 0, 6.2832); ctx.fill();
    ctx.beginPath(); ctx.arc(x + 2 + ex, y - 8, 1, 0, 6.2832); ctx.fill();
    accessory(ch, x, y);
    ctx.font = '600 10px ' + FONT; ctx.textAlign = 'center';
    var tw = ctx.measureText(ch.name).width + 12;
    roundRect(x - tw / 2, y - 30, tw, 14, 7); ctx.fillStyle = 'rgba(15,13,41,.72)'; ctx.fill();
    ctx.fillStyle = ch.color; var lbl = ch.name.length > 18 ? ch.name.slice(0, 17) + '…' : ch.name; ctx.fillText(lbl, x, y - 20);
    if (ch.state === 'think') thinkBubble(x, y - 40, time);
    else if (ch.state === 'reply') replyBubble(x, y - 44, ch.color);
  }

  var last = 0, busy = 0;
  function frame(time) {
    var dt = Math.min(3, (time - last) / 16.7) || 1; last = time;
    ctx.clearRect(0, 0, W, H);
    drawGround(time); drawTower(time);
    busy = 0;
    var order = chars.slice().sort(function (a, b) { return a.y - b.y; });
    for (var i = 0; i < order.length; i++) { stepChar(order[i], dt, time); if (order[i].state !== 'idle') busy++; drawChar(order[i], time); }
    requestAnimationFrame(frame);
  }

  function fmt(n) { return Math.round(n || 0).toLocaleString('en-US'); }
  var shown = { requests: 0, tokens: 0, cost: 0 };
  function hud(k) {
    if (k) { shown._r = k.requests; shown._t = k.tokens; shown._c = k.cost_uzs; }
    shown.requests += ((shown._r || 0) - shown.requests) * 0.15;
    shown.tokens += ((shown._t || 0) - shown.tokens) * 0.15;
    shown.cost += ((shown._c || 0) - shown.cost) * 0.15;
    set('requests', shown.requests); set('tokens', shown.tokens); set('cost', shown.cost); set('busy', busy);
  }
  function set(k, v) { var el = document.querySelector('[data-hud="' + k + '"]'); if (el) el.textContent = fmt(v); }
  var clock = document.getElementById('townClock');
  function tick() { clock.textContent = new Date().toLocaleTimeString('en-GB'); }

  // Legend
  var seen = {}, legHtml = '';
  chars.forEach(function (c) { if (!seen[c.role]) { seen[c.role] = 1; legHtml += '<span class="town-leg"><i style="background:' + ROLES[c.role].color + ';box-shadow:0 0 8px ' + ROLES[c.role].color + '99"></i>' + ROLES[c.role].label + '</span>'; } });
  document.getElementById('townLegend').innerHTML = legHtml;

  function poll() {
    fetch(T.streamUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        hud(d.kpis || {});
        var fresh = 0;
        (d.recent || []).forEach(function (e) { if (e.ts > lastTs) fresh++; });
        (d.recent || []).forEach(function (e) { if (e.ts > lastTs) lastTs = e.ts; });
        for (var i = 0; i < Math.min(fresh, 4); i++) setTimeout(triggerWork, i * 350);
      })
      .catch(function () {});
  }

  layout(); hud(T.kpis || {});
  requestAnimationFrame(frame);
  setInterval(tick, 1000); tick();
  setInterval(function () { hud(); }, 120);
  setTimeout(triggerWork, 800);
  var timer = setInterval(poll, 4500);
  document.addEventListener('visibilitychange', function () { clearInterval(timer); if (!document.hidden) { poll(); timer = setInterval(poll, 4500); } });
})();
</script>
@endpush
