@props([
    'sticky' => true,
    'class' => '',
    'mobileCards' => true,
])

<div {{ $attributes->merge(['class' => 'ds-table-wrap table-wrap table-responsive ' . ($mobileCards ? 'mobile-card-table ' : '') . $class]) }}>
    {{ $slot }}
</div>
