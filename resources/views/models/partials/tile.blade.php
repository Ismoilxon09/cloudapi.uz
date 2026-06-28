@php
  $finalInput = $model->cost_input_usd * (1 + $model->margin_percent / 100);
  $finalOutput = $model->cost_output_usd * (1 + $model->margin_percent / 100);
  $inputUzs = $finalInput * $model->usd_to_uzs;
  $outputUzs = $finalOutput * $model->usd_to_uzs;
@endphp

<a href="{{ route('models.show', $model->model_id) }}" class="model-tile">
  <div class="model-tile-head">
    <div class="model-logo">
      @include('models.partials.logo', ['modelId' => $model->model_id])
    </div>
    <div class="model-info">
      <div class="model-name" title="{{ $model->display_name }}">{{ $model->display_name }}</div>
      <div class="model-id">{{ $model->model_id }}</div>
    </div>
  </div>

  <div class="model-badges">
    @if($model->is_free)
      <span class="model-badge free">
        <span class="material-icons-round">free_breakfast</span>
        Free
      </span>
    @endif
    @if($model->is_featured)
      <span class="model-badge featured">
        <span class="material-icons-round">star</span>
        Featured
      </span>
    @endif
    @if($model->category && $model->category !== 'chat')
      <span class="model-badge cat-{{ $model->category }}">{{ ucfirst($model->category) }}</span>
    @endif
    @if(is_array($model->capabilities))
      @foreach(array_slice($model->capabilities, 0, 2) as $cap)
        <span class="model-badge">{{ str_replace('_', ' ', $cap) }}</span>
      @endforeach
    @endif
  </div>

  @if($model->description)
    <div class="model-desc">{{ $model->description }}</div>
  @endif

  <div class="model-prices">
    <div class="model-price">
      <div class="model-price-label">{{ __('models.pricing.input') }}</div>
      <div class="model-price-value {{ $model->is_free ? 'free' : '' }}">
        @if($model->is_free)
          Free
        @else
          {{ number_format($inputUzs, 0, '.', ' ') }}<span class="meta-suffix">/M so'm</span>
        @endif
      </div>
      @if(!$model->is_free)
        <div class="model-price-usd">${{ number_format($finalInput, 2) }}</div>
      @endif
    </div>
    <div class="model-price">
      <div class="model-price-label">{{ __('models.pricing.output') }}</div>
      <div class="model-price-value {{ $model->is_free ? 'free' : '' }}">
        @if($model->is_free)
          Free
        @else
          {{ number_format($outputUzs, 0, '.', ' ') }}<span class="meta-suffix">/M so'm</span>
        @endif
      </div>
      @if(!$model->is_free)
        <div class="model-price-usd">${{ number_format($finalOutput, 2) }}</div>
      @endif
    </div>
  </div>

  @if($model->context_length)
    <div class="model-context">
      <span class="material-icons-round">data_array</span>
      {{ number_format($model->context_length / 1000, 0) }}K context
    </div>
  @endif
</a>