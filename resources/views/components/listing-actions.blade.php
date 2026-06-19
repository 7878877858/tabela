{{-- Reusable listing row actions — desktop table cell, mobile card footer (right-aligned). --}}
<td {{ $attributes->merge(['class' => 'mobile-card-actions erp-listing__actions', 'data-label' => '']) }}>
    <div class="mobile-card-actions__group">
        {{ $slot }}
    </div>
</td>
