{{--
  CloudAPI Logo Partial
  Usage:
    @include('partials.logo')                          {{-- Default 32px mark + wordmark --}}
    @include('partials.logo', ['size' => 'sm'])        {{-- Small --}}
    @include('partials.logo', ['size' => 'lg'])        {{-- Large --}}
    @include('partials.logo', ['mark_only' => true])   {{-- Icon only --}}
    @include('partials.logo', ['color' => '#FFFFFF'])  {{-- Custom color --}}
--}}

@php
  $size = $size ?? 'md';
  $markOnly = $markOnly ?? false;
  $color = $color ?? 'currentColor';
  
  $heights = [
    'xs' => 16,
    'sm' => 22,
    'md' => 28,
    'lg' => 36,
    'xl' => 48,
  ];
  $h = $heights[$size] ?? 28;
@endphp

@if($markOnly)
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" 
       style="height:{{ $h }}px;width:auto;" fill="{{ $color }}">
    <rect x="0" y="0" width="36" height="36" rx="8"/>
    <rect x="0" y="82" width="36" height="36" rx="8"/>
    <rect x="0" y="164" width="36" height="36" rx="8"/>
    <path d="M 36 18 C 90 18, 110 60, 135 90" stroke="{{ $color }}" stroke-width="14" fill="none" stroke-linecap="round"/>
    <path d="M 36 182 C 90 182, 110 140, 135 110" stroke="{{ $color }}" stroke-width="14" fill="none" stroke-linecap="round"/>
    <rect x="36" y="93" width="100" height="14" rx="3"/>
    <rect x="130" y="65" width="70" height="70" rx="14"/>
    <line x1="200" y1="100" x2="230" y2="100" stroke="{{ $color }}" stroke-width="10" stroke-linecap="round"/>
    <polygon points="225,90 240,100 225,110"/>
  </svg>
@else
  <span style="display:inline-flex;align-items:center;gap:{{ $h * 0.3 }}px;">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" 
         style="height:{{ $h }}px;width:auto;" fill="{{ $color }}">
      <rect x="0" y="0" width="36" height="36" rx="8"/>
      <rect x="0" y="82" width="36" height="36" rx="8"/>
      <rect x="0" y="164" width="36" height="36" rx="8"/>
      <path d="M 36 18 C 90 18, 110 60, 135 90" stroke="{{ $color }}" stroke-width="14" fill="none" stroke-linecap="round"/>
      <path d="M 36 182 C 90 182, 110 140, 135 110" stroke="{{ $color }}" stroke-width="14" fill="none" stroke-linecap="round"/>
      <rect x="36" y="93" width="100" height="14" rx="3"/>
      <rect x="130" y="65" width="70" height="70" rx="14"/>
      <line x1="200" y1="100" x2="230" y2="100" stroke="{{ $color }}" stroke-width="10" stroke-linecap="round"/>
      <polygon points="225,90 240,100 225,110"/>
    </svg>
    <span style="font-family:Inter,-apple-system,sans-serif;font-size:{{ $h * 0.85 }}px;font-weight:800;letter-spacing:-0.04em;line-height:1;color:{{ $color }};">cloud<span style="font-weight:500;opacity:0.6;">api</span></span>
  </span>
@endif