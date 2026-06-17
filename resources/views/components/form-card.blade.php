@props([
    'title' => '',
    'icon' => null,
    'headerClass' => '',
    'flush' => false,
])

<div {{ $attributes->merge(['class' => 'ds-card ds-form-card' . ($flush ? ' ds-card--flush' : '')]) }}>
    @if($title)
    <div class="ds-card__header {{ $headerClass }}">
        {{ $icon ? $icon.' ' : '' }}{{ $title }}
    </div>
    @endif
    <div class="ds-card__body">
        {{ $slot }}
    </div>
    @isset($footer)
    <div class="ds-card__footer">
        {{ $footer }}
    </div>
    @endisset
</div>
