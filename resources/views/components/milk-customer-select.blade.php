@props([
    'name' => 'customer_id',
    'value' => null,
    'customers' => [],
    'placeholder' => null,
    'required' => false,
])

@php
    $placeholder = $placeholder ?? __('milk_flow.select_customer');
@endphp

<select
    name="{{ $name }}"
    class="form-control milk-customer-select"
    data-placeholder="{{ $placeholder }}"
    @if($required) required @endif
>
    <option value="">{{ $placeholder }}</option>
    @foreach($customers as $customer)
        <option value="{{ $customer->id }}" @selected((string) $value === (string) $customer->id)>
            {{ $customer->display_label }}
        </option>
    @endforeach
</select>
