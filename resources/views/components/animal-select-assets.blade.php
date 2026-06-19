@once
@php
    $animalCssVer = @filemtime(public_path('static/css/animal-select.css')) ?: '1';
    $animalJsVer = @filemtime(public_path('static/js/animal-select.js')) ?: '1';
@endphp
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('static/css/animal-select.css') }}?v={{ $animalCssVer }}">
@include('partials._animals_registry')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="{{ asset('static/js/animal-select.js') }}?v={{ $animalJsVer }}"></script>
@endonce
