@once
    @php
        $milkCustomerJsVer = @filemtime(public_path('static/js/milk-customer-select.js')) ?: '1';
    @endphp
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
        <link rel="stylesheet" href="{{ asset('static/css/animal-select.css') }}">
    @endpush
    @unless(request()->routeIs('daily-reports.create', 'daily-reports.edit'))
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
            <script src="{{ asset('static/js/milk-customer-select.js') }}?v={{ $milkCustomerJsVer }}"></script>
        @endpush
    @endunless
@endonce
