@props([
    'label' => '',
    'value' => '',
    'sub' => null,
    'color' => 'blue',
    'icon' => null,
    'variant' => 'dashboard',
    'valueClass' => '',
])

@if($variant === 'plain')
<div {{ $attributes->merge(['class' => 'ds-stat-card stat-card']) }}>
    @if($label)
    <div class="ds-stat-card__label label">{{ $icon ? $icon.' ' : '' }}{{ $label }}</div>
    @endif
    <div class="ds-stat-card__value value {{ $valueClass }}">{{ $value }}</div>
    @if($sub)
    <div class="ds-stat-card__sub sub">{{ $sub }}</div>
    @endif
</div>
@else
<div {{ $attributes->merge(['class' => 'dashboard-card ' . $color]) }}>
    <div class="card-top">
        @if($icon)<span class="icon">{{ $icon }}</span>@endif
        <span class="title">{{ $label }}</span>
    </div>
    <div class="card-bottom {{ $valueClass }}">{{ $value }}</div>
    @if($sub)
    <div style="text-align:center;font-size:11px;color:#64748b;padding-bottom:8px;">{{ $sub }}</div>
    @endif
</div>
@endif
