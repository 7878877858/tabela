@props([
    'icon' => '📊',
    'value' => '0',
    'label' => '',
    'accent' => 'blue',
    'sub' => null,
    'trend' => null,
])

<div {{ $attributes->merge(['class' => 'erp-kpi erp-kpi--' . $accent]) }}>
    <div class="erp-kpi__icon-wrap" aria-hidden="true">{{ $icon }}</div>
    <div class="erp-kpi__body">
        <div class="erp-kpi__value">{{ $value }}</div>
        <div class="erp-kpi__label">{{ $label }}</div>
        @if($sub)
        <div class="erp-kpi__sub">{{ $sub }}</div>
        @endif
        @if($trend)
        <div class="erp-kpi__trend">{{ $trend }}</div>
        @endif
    </div>
</div>
