@extends('admin.layout')

@section('title', 'Vantage')
@section('page_title', 'Vantage — Platforma kuzatuvi')

@push('styles')
<style>
.av { padding: 24px; max-width: 1400px; margin: 0 auto; }
.av-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
.av-live { display: inline-flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 600; color: var(--text-muted); }
.av-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--success); animation: avp 1.8s infinite; }
@keyframes avp { 0%,100%{opacity:1;} 50%{opacity:.4;} }
.kpis { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 18px; }
@media (max-width: 900px) { .kpis { grid-template-columns: repeat(2, 1fr); } }
.kpi { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; padding: 15px 16px; }
.kpi .k { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--text-subtle); }
.kpi .v { font-size: 24px; font-weight: 800; color: var(--text-strong); margin-top: 6px; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; }
.grid { display: grid; grid-template-columns: 1fr 320px; gap: 16px; align-items: start; }
@media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
.card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; padding: 18px; margin-bottom: 16px; }
.card h3 { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: var(--text-subtle); margin-bottom: 14px; }
.chart { display: flex; align-items: flex-end; gap: 3px; height: 120px; }
.bar { flex: 1; background: var(--accent); border-radius: 3px 3px 0 0; min-height: 2px; opacity: .82; }
.axis { display: flex; justify-content: space-between; margin-top: 7px; font-size: 10px; color: var(--text-subtle); }
.feed-row { display: grid; grid-template-columns: 60px 1fr auto auto; gap: 10px; align-items: center; padding: 9px 0; border-top: 1px solid var(--border); font-size: 12.5px; }
.feed-row:first-child { border-top: none; }
.src { font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 2px 7px; border-radius: 6px; text-align: center; }
.src-api { background: rgba(37,99,235,.12); color: var(--accent); }
.src-agent { background: rgba(16,185,129,.12); color: var(--success); }
.fmodel { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fnum { font-family: 'JetBrains Mono', monospace; color: var(--text-muted); white-space: nowrap; }
.ftime { font-size: 11px; color: var(--text-subtle); text-align: right; }
.list-row { display: flex; justify-content: space-between; gap: 10px; align-items: center; padding: 8px 0; border-top: 1px solid var(--border); font-size: 12.5px; }
.list-row:first-child { border-top: none; }
.list-row .nm { color: var(--text-strong); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.list-row .rt { font-family: 'JetBrains Mono', monospace; color: var(--text-muted); }
.tm-bar { height: 5px; border-radius: 3px; background: var(--accent); opacity: .8; margin-top: 5px; }
</style>
@endpush

@section('content')
<div class="av">
  <div class="av-head">
    <div>
      <h1 class="page-title">Vantage</h1>
      <p class="page-subtitle">Butun platforma bo'yicha jonli AI faoliyati</p>
    </div>
    <div class="av-live"><span class="av-dot"></span> Jonli · <span id="avClock">hozir</span></div>
  </div>

  <div class="kpis">
    <div class="kpi"><div class="k">So'rov · bugun</div><div class="v" data-hud="requests">0</div></div>
    <div class="kpi"><div class="k">Token</div><div class="v" data-hud="tokens">0</div></div>
    <div class="kpi"><div class="k">Xarajat (so'm)</div><div class="v" data-hud="cost">0</div></div>
    <div class="kpi"><div class="k">Faol userlar</div><div class="v" data-hud="users">0</div></div>
    <div class="kpi"><div class="k">Faol agentlar</div><div class="v" data-hud="agents">0</div></div>
  </div>

  <div class="grid">
    <div>
      <div class="card">
        <h3>Xarajat · so'nggi 24 soat</h3>
        <div class="chart" id="avChart"></div>
        <div class="axis"><span>24s oldin</span><span>12s</span><span>hozir</span></div>
      </div>
      <div class="card">
        <h3>Jonli oqim</h3>
        <div id="avFeed"></div>
      </div>
    </div>

    <div>
      <div class="card">
        <h3>Top modellar · 7 kun</h3>
        @php $max = max(1, collect($topModels)->max('count') ?? 1); @endphp
        @forelse($topModels as $m)
          <div style="padding:8px 0;border-top:1px solid var(--border);">
            <div class="list-row" style="border:none;padding:0;"><span class="nm fmodel" style="max-width:180px">{{ $m['model'] }}</span><b class="rt">{{ number_format($m['count']) }}</b></div>
            <div class="tm-bar" style="width:{{ max(6, round($m['count']/$max*100)) }}%"></div>
          </div>
        @empty
          <div style="color:var(--text-muted);font-size:12.5px;">Ma'lumot yo'q</div>
        @endforelse
      </div>

      <div class="card">
        <h3>Top userlar · 7 kun</h3>
        @forelse($topUsers as $u)
          <div class="list-row"><span class="nm">{{ $u['name'] }}</span><span class="rt">{{ number_format($u['cost'],0,'.',' ') }} so'm</span></div>
        @empty
          <div style="color:var(--text-muted);font-size:12.5px;">Ma'lumot yo'q</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
@php $avBoot = ['kpis'=>$kpis,'recent'=>$recent,'series'=>$series,'streamUrl'=>route('admin.vantage.stream')]; @endphp
window.AV = {!! json_encode($avBoot, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!};
(function(){
  var V = window.AV;
  var fmt = function(n){ return (n||0).toLocaleString('en-US'); };
  var esc = function(s){ var d=document.createElement('div'); d.textContent=s==null?'':String(s); return d.innerHTML; };
  function ago(iso){ if(!iso) return ''; var s=Math.max(0,Math.floor((Date.now()-new Date(iso).getTime())/1000)); if(s<60)return s+'s'; if(s<3600)return Math.floor(s/60)+'m'; if(s<86400)return Math.floor(s/3600)+'h'; return Math.floor(s/86400)+'k'; }
  function kpis(k){ ['requests','tokens','cost','users','agents'].forEach(function(key){ var el=document.querySelector('[data-hud="'+key+'"]'); if(el){ var v = key==='cost'?k.cost_uzs:k[key]; el.textContent=fmt(v); } }); }
  function chart(series){ var el=document.getElementById('avChart'); if(!el)return; var max=Math.max.apply(null,series.map(function(p){return p.cost;}).concat([0.0001]));
    el.innerHTML=series.map(function(p){ var h=Math.max(2,Math.round(p.cost/max*120)); return '<div class="bar" style="height:'+h+'px" title="'+p.label+' · '+fmt(Math.round(p.cost))+' so\'m"></div>'; }).join(''); }
  function feed(recent){ var el=document.getElementById('avFeed'); if(!el)return; if(!recent||!recent.length){ el.innerHTML='<div style="color:var(--text-muted);font-size:12.5px;padding:12px 0">Faoliyat yo\'q</div>'; return; }
    el.innerHTML=recent.map(function(e){ var cls=e.source==='API'?'src-api':'src-agent';
      return '<div class="feed-row"><span class="src '+cls+'">'+esc(e.source)+'</span><span class="fmodel">'+esc(e.model||'—')+'</span><span class="fnum">'+fmt(e.tokens)+' tk · '+fmt(e.cost)+' so\'m</span><span class="ftime">'+ago(e.at)+'</span></div>'; }).join(''); }
  function paint(d){ kpis(d.kpis||{}); chart(d.series||[]); feed(d.recent||[]); var c=document.getElementById('avClock'); if(c)c.textContent=new Date().toLocaleTimeString('en-GB'); }
  paint(V);
  function poll(){ fetch(V.streamUrl,{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){return r.json();}).then(paint).catch(function(){}); }
  var t=setInterval(poll,5000);
  document.addEventListener('visibilitychange',function(){ clearInterval(t); if(!document.hidden){ poll(); t=setInterval(poll,5000); } });
})();
</script>
@endpush
