@extends('layouts.app')

@section('title', 'Vantage — CloudAPI')

@push('styles')
<style>
.vtg-page { max-width: 1240px; margin: 0 auto; padding: 32px 24px 64px; }
.vtg-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
.vtg-live { display: inline-flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 600; color: var(--text-muted); }
.vtg-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--success); box-shadow: 0 0 0 0 rgba(16,185,129,.5); animation: vtgPulse 1.8s infinite; }
@keyframes vtgPulse { 0%{box-shadow:0 0 0 0 rgba(16,185,129,.5);} 70%{box-shadow:0 0 0 7px rgba(16,185,129,0);} 100%{box-shadow:0 0 0 0 rgba(16,185,129,0);} }

.vtg-kpis { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 20px; }
@media (max-width: 900px) { .vtg-kpis { grid-template-columns: repeat(2, 1fr); } }
.kpi { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: var(--r-lg); padding: 16px 18px; }
.kpi-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-subtle); display: flex; align-items: center; gap: 6px; }
.kpi-label .material-icons-round { font-size: 15px; }
.kpi-val { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-strong); margin-top: 8px; line-height: 1; }
.kpi-sub { font-size: 11px; color: var(--text-subtle); margin-top: 4px; }

.vtg-grid { display: grid; grid-template-columns: 1fr 320px; gap: 18px; align-items: start; }
@media (max-width: 900px) { .vtg-grid { grid-template-columns: 1fr; } }
.panel { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: var(--r-lg); padding: 20px; margin-bottom: 18px; }
.panel-title { font-size: 13px; font-weight: 700; color: var(--text-strong); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
.panel-hint { font-size: 12px; color: var(--text-subtle); margin-bottom: 16px; }

.chart { display: flex; align-items: flex-end; gap: 3px; height: 130px; }
.chart-bar { flex: 1; background: var(--accent); border-radius: 3px 3px 0 0; min-height: 2px; opacity: .85; transition: height .4s var(--ease); position: relative; }
.chart-bar:hover { opacity: 1; }
.chart-axis { display: flex; justify-content: space-between; margin-top: 8px; font-size: 10px; color: var(--text-subtle); }

.feed { display: flex; flex-direction: column; }
.feed-row { display: grid; grid-template-columns: 62px 1fr auto auto; gap: 10px; align-items: center; padding: 10px 0; border-top: 1px solid var(--border); font-size: 12.5px; }
.feed-row:first-child { border-top: none; }
.feed-src { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; padding: 2px 7px; border-radius: 6px; text-align: center; }
.src-api { background: rgba(37,99,235,.12); color: var(--accent); }
.src-agent { background: rgba(16,185,129,.12); color: var(--success); }
.feed-model { color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; }
.feed-num { color: var(--text-muted); font-variant-numeric: tabular-nums; white-space: nowrap; }
.feed-time { color: var(--text-subtle); font-size: 11px; white-space: nowrap; min-width: 42px; text-align: right; }
.tm-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 0; border-top: 1px solid var(--border); font-size: 12.5px; }
.tm-row:first-child { border-top: none; }
.tm-bar { height: 5px; border-radius: 3px; background: var(--accent); opacity: .8; }
.vtg-empty { text-align: center; color: var(--text-subtle); font-size: 12.5px; padding: 28px 0; }
</style>
@endpush

@section('content')
<div class="vtg-page fade-up">

  <div class="vtg-head">
    <div>
      <div class="page-title" style="display:flex;align-items:center;gap:10px;">
        <span class="material-icons-round" style="font-size:24px;">radar</span> Vantage
      </div>
      <div class="page-subtitle">CloudAPI orqali o'tayotgan barcha AI faoliyatini jonli kuzating.</div>
    </div>
    <div class="flex items-center gap-3">
      <a href="{{ route('vantage.town') }}" class="btn btn-primary">
        <span class="material-icons-round">hub</span> AI Town
      </a>
      <div class="vtg-live"><span class="vtg-dot"></span> Jonli · <span id="vtgClock">hozir</span></div>
    </div>
  </div>

  {{-- KPI --}}
  <div class="vtg-kpis" id="vtgKpis">
    @php
      $k = $kpis;
      $tiles = [
        ['label'=>'So\'rovlar','icon'=>'bolt','key'=>'requests','sub'=>'bugun'],
        ['label'=>'Tokenlar','icon'=>'data_usage','key'=>'tokens','sub'=>'bugun'],
        ['label'=>'Xarajat','icon'=>'payments','key'=>'cost_uzs','sub'=>'so\'m · bugun'],
        ['label'=>'Modellar','icon'=>'memory','key'=>'models','sub'=>'faol'],
        ['label'=>'Agentlar','icon'=>'smart_toy','key'=>'agents_active','sub'=>'faol'],
      ];
    @endphp
    @foreach($tiles as $t)
      <div class="kpi">
        <div class="kpi-label"><span class="material-icons-round">{{ $t['icon'] }}</span> {{ $t['label'] }}</div>
        <div class="kpi-val" data-kpi="{{ $t['key'] }}">{{ number_format($k[$t['key']] ?? 0) }}</div>
        <div class="kpi-sub">{{ $t['sub'] }}</div>
      </div>
    @endforeach
  </div>

  <div class="vtg-grid">
    {{-- LEFT --}}
    <div>
      <div class="panel">
        <div class="panel-title">Xarajat · so'nggi 24 soat</div>
        <div class="panel-hint">Soatlik xarajat (so'm)</div>
        <div class="chart" id="vtgChart"></div>
        <div class="chart-axis"><span>24s oldin</span><span>12s</span><span>hozir</span></div>
      </div>

      <div class="panel">
        <div class="panel-title">Jonli oqim</div>
        <div class="panel-hint">So'nggi AI so'rovlari — real vaqtda</div>
        <div class="feed" id="vtgFeed"></div>
      </div>
    </div>

    {{-- RIGHT --}}
    <div>
      <div class="panel">
        <div class="panel-title">Top modellar</div>
        <div class="panel-hint">So'nggi 7 kun</div>
        <div id="vtgTopModels">
          @forelse($topModels as $m)
            @php $max = max(1, collect($topModels)->max('count')); @endphp
            <div style="padding:9px 0;border-top:1px solid var(--border);">
              <div class="flex justify-between" style="font-size:12.5px;margin-bottom:6px;">
                <span class="feed-model" style="max-width:180px;">{{ $m['model'] }}</span>
                <b>{{ number_format($m['count']) }}</b>
              </div>
              <div class="tm-bar" style="width:{{ max(6, round($m['count']/$max*100)) }}%;"></div>
              <div style="font-size:11px;color:var(--text-subtle);margin-top:4px;">{{ number_format($m['cost'],0) }} so'm</div>
            </div>
          @empty
            <div class="vtg-empty">Hali ma'lumot yo'q</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
@php $vtgBoot = ['kpis'=>$kpis,'recent'=>$recent,'series'=>$series,'streamUrl'=>route('vantage.stream')]; @endphp
window.VANTAGE = {!! json_encode($vtgBoot, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!};
(function(){
  var V = window.VANTAGE;
  var fmt = function(n){ return (n||0).toLocaleString('en-US'); };
  var esc = function(s){ var d=document.createElement('div'); d.textContent=s==null?'':String(s); return d.innerHTML; };

  function ago(iso){
    if(!iso) return '';
    var s = Math.max(0, Math.floor((Date.now() - new Date(iso).getTime())/1000));
    if(s<60) return s+'s';
    if(s<3600) return Math.floor(s/60)+'m';
    if(s<86400) return Math.floor(s/3600)+'h';
    return Math.floor(s/86400)+'k';
  }

  function renderKpis(k){
    document.querySelectorAll('[data-kpi]').forEach(function(el){
      var key = el.getAttribute('data-kpi');
      el.textContent = fmt(k[key]);
    });
  }

  function renderChart(series){
    var el = document.getElementById('vtgChart'); if(!el) return;
    var max = Math.max.apply(null, series.map(function(p){return p.cost;}).concat([0.0001]));
    el.innerHTML = series.map(function(p){
      var h = Math.max(2, Math.round(p.cost/max*130));
      return '<div class="chart-bar" style="height:'+h+'px" title="'+p.label+' · '+fmt(Math.round(p.cost))+' so\'m ('+p.requests+' so\'rov)"></div>';
    }).join('');
  }

  function renderFeed(recent){
    var el = document.getElementById('vtgFeed'); if(!el) return;
    if(!recent || !recent.length){ el.innerHTML = '<div class="vtg-empty">Hali faoliyat yo\'q. API yoki agent ishlatilganda shu yerda ko\'rinadi.</div>'; return; }
    el.innerHTML = recent.map(function(e){
      var cls = e.source==='API' ? 'src-api' : 'src-agent';
      return '<div class="feed-row">'
        + '<span class="feed-src '+cls+'">'+esc(e.source)+'</span>'
        + '<span class="feed-model">'+esc(e.model||'—')+'</span>'
        + '<span class="feed-num">'+fmt(e.tokens)+' tk · '+fmt(e.cost)+' so\'m</span>'
        + '<span class="feed-time">'+ago(e.at)+'</span>'
        + '</div>';
    }).join('');
  }

  function paint(data){
    renderKpis(data.kpis); renderChart(data.series); renderFeed(data.recent);
    var c=document.getElementById('vtgClock'); if(c) c.textContent = new Date().toLocaleTimeString('en-GB');
  }

  paint(V);

  function poll(){
    fetch(V.streamUrl, {headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){ return r.json(); })
      .then(function(d){ paint(d); })
      .catch(function(){});
  }
  var timer = setInterval(poll, 5000);
  document.addEventListener('visibilitychange', function(){
    clearInterval(timer);
    if(!document.hidden){ poll(); timer = setInterval(poll, 5000); }
  });
})();
</script>
@endpush
