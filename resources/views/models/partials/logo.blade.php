@php
  $provider = explode('/', $modelId)[0] ?? 'unknown';
  $provider = strtolower($provider);
@endphp

@switch($provider)
  @case('openai')
    <svg viewBox="0 0 24 24" fill="#10A37F"><path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.682zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/></svg>
    @break

  @case('anthropic')
    <svg viewBox="0 0 24 24" fill="#D97757"><path d="M17.304 3.541h-3.672l6.696 16.918H24L17.304 3.541zm-10.608 0L0 20.459h3.744l1.37-3.553h7.005l1.369 3.553h3.744L10.536 3.541H6.696zm-.371 10.223L8.616 7.82l2.291 5.945H6.325z"/></svg>
    @break

  @case('google')
    <svg viewBox="0 0 24 24" fill="none">
      <path d="M12 2L2 7v10l10 5 10-5V7L12 2z" fill="#4285F4"/>
      <path d="M12 2L2 7l10 5 10-5-10-5z" fill="#34A853" opacity="0.6"/>
      <circle cx="12" cy="12" r="3" fill="white"/>
    </svg>
    @break

  @case('meta-llama')
  @case('meta')
    <svg viewBox="0 0 24 24" fill="#0668E1"><path d="M12 1.5C5.65 1.5.5 6.65.5 13S5.65 24.5 12 24.5 23.5 19.35 23.5 13 18.35 1.5 12 1.5zM6.86 17.5c-1.18-.81-1.92-2.17-1.92-3.78 0-2.43 1.97-4.4 4.4-4.4 1.61 0 3.03.86 3.81 2.15-.5.31-.87.66-1.13.92-.55-.94-1.59-1.58-2.78-1.58-1.77 0-3.21 1.44-3.21 3.21 0 1.16.62 2.19 1.56 2.74-.27.27-.6.55-.73.74zm10.28 0c-.13-.19-.46-.47-.73-.74.94-.55 1.56-1.58 1.56-2.74 0-1.77-1.44-3.21-3.21-3.21-1.19 0-2.23.64-2.78 1.58-.26-.26-.63-.61-1.13-.92.78-1.29 2.2-2.15 3.81-2.15 2.43 0 4.4 1.97 4.4 4.4 0 1.61-.74 2.97-1.92 3.78z"/></svg>
    @break

  @case('deepseek')
    <svg viewBox="0 0 24 24" fill="#4D6BFE"><circle cx="12" cy="12" r="10"/><path d="M8 8h4v8H8zM14 8h2v8h-2z" fill="white"/></svg>
    @break

  @case('mistralai')
  @case('mistral')
    <svg viewBox="0 0 24 24" fill="none">
      <rect x="2" y="3" width="3" height="18" fill="#FF7A00"/>
      <rect x="7" y="3" width="3" height="18" fill="#FF9900"/>
      <rect x="12" y="3" width="3" height="18" fill="#FFAA00"/>
      <rect x="17" y="3" width="3" height="18" fill="#FFCC00"/>
    </svg>
    @break

  @case('qwen')
    <svg viewBox="0 0 24 24" fill="#615CED"><path d="M12 2L4 7v10l8 5 8-5V7l-8-5zm0 2.5L18 8v8l-6 3.5-6-3.5V8l6-3.5zm0 3L8 9v6l4 2 4-2V9l-4-1.5z"/></svg>
    @break

  @case('cohere')
    <svg viewBox="0 0 24 24" fill="#FF7759"><circle cx="12" cy="12" r="10"/><path d="M8 12c0-2.21 1.79-4 4-4s4 1.79 4 4-1.79 4-4 4-4-1.79-4-4z" fill="white"/></svg>
    @break

  @case('x-ai')
    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 3l8 9L3 21h2.7l6.6-7.5L18.9 21h2.1L13 12l8-9h-2.7l-6.6 7.5L5.1 3H3z"/></svg>
    @break

  @case('perplexity')
    <svg viewBox="0 0 24 24" fill="#1FB8CD"><path d="M12 2L2 7l10 5 10-5-10-5zm0 8L4 14l8 4 8-4-8-4z"/></svg>
    @break

  @case('microsoft')
    <svg viewBox="0 0 24 24">
      <rect x="2" y="2" width="9" height="9" fill="#F25022"/>
      <rect x="13" y="2" width="9" height="9" fill="#7FBA00"/>
      <rect x="2" y="13" width="9" height="9" fill="#00A4EF"/>
      <rect x="13" y="13" width="9" height="9" fill="#FFB900"/>
    </svg>
    @break

  @case('nvidia')
    <svg viewBox="0 0 24 24" fill="#76B900"><path d="M9.59 6.5v3.5l4.41-2c.27-.12.5.06.5.33v3.34c0 .27-.23.45-.5.33l-4.41-2v3.5h2.41l4.5-7.5h-6.91z"/></svg>
    @break

  @case('amazon')
    <svg viewBox="0 0 24 24" fill="#FF9900"><path d="M14.5 11.5c0 2.5-1.5 4-3.5 4s-3.5-1.5-3.5-4 1.5-4 3.5-4 3.5 1.5 3.5 4zM2 18c4 3 14 3 18 0v2c-4 3-14 3-18 0v-2zm0-4c4 3 14 3 18 0v2c-4 3-14 3-18 0v-2z"/></svg>
    @break

  @default
    {{-- Default: provider initial in colored circle --}}
    @php
      $initial = strtoupper(substr($provider, 0, 2));
      $colors = ['#374151', '#1F2937', '#4B5563', '#111827'];
      $color = $colors[abs(crc32($provider)) % count($colors)];
    @endphp
    <svg viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="{{ $color }}"/><text x="12" y="16" text-anchor="middle" font-family="Inter" font-weight="700" font-size="9" fill="white">{{ $initial }}</text></svg>
@endswitch