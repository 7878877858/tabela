@props([
    'title' => '',
    'subtitle' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'ds-page-header page-header']) }}>
    <div class="ds-page-header__content">
        <h2 class="ds-page-header__title">
            {{ $icon ? $icon.' ' : '' }}{{ $title }}
        </h2>
        @if($subtitle)
        <p class="ds-page-header__subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions) && trim($actions) !== '')
    <div class="ds-page-header__actions page-header-actions">
        {{ $actions }}
    </div>
    @endif
</div>
