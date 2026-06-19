@extends('layouts.app')
@section('title', __('buffalo.all_buffaloes'))

@section('content')

<x-section-header :title="__('buffalo.all_buffaloes')" icon="🐃">
    <x-slot:actions>
        <a href="{{ route('buffalo.create') }}" class="btn btn-primary">➕ {{ __('common.add_buffalo') }}</a>
    </x-slot:actions>
</x-section-header>

<div class="animal-list-page">
    <x-form-card :title="__('buffalo.all_buffaloes')" icon="🐃" :flush="true">
        <x-erp-listing :per-page="25" id="buffalo-list" :search="false">
            <x-slot:filters>
                <div id="buffaloAnimalTabs" class="erp-filter-tabs" role="tablist" aria-label="Animal type filter"></div>
            </x-slot:filters>
            <x-slot:toolbar>
                <div class="erp-listing__search-field">
                    <span class="erp-listing__search-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </span>
                    <input type="search" id="buffaloAnimalSearch" class="erp-listing__search-input" placeholder="{{ __('common.search_placeholder') }}" autocomplete="off">
                </div>
            </x-slot:toolbar>

            <div class="table-responsive mobile-card-table ds-table-wrap">
                <table class="ds-table animal-list-table" id="buffaloListTable">
                    <thead>
                        <tr>
                            <th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th>
                            <th>{{ __('buffalo.tag_number') }}</th>
                            <th>પ્રકાર</th>
                            <th>{{ __('buffalo.name') }}</th>
                            <th>{{ __('buffalo.status') }}</th>
                            <th>{{ __('buffalo.milk') }}</th>
                            <th>{{ __('buffalo.this_month') }}</th>
                            <th class="animal-list-actions-col"></th>
                        </tr>
                    </thead>
                    <tbody id="buffaloListBody"></tbody>
                </table>
            </div>
        </x-erp-listing>
    </x-form-card>
</div>

<script type="application/json" id="buffaloListJson">@json($animalsJson)</script>
@php
    $buffaloListConfig = [
        'csrf' => csrf_token(),
        'deleteConfirm' => __('buffalo.delete_confirm'),
        'createUrl' => route('buffalo.create'),
        'srNoLabel' => __('common.sr_no'),
        'searchPlaceholder' => __('common.search_placeholder'),
        'show' => __('common.show'),
        'previous' => __('common.previous'),
        'next' => __('common.next'),
        'showingRecords' => __('common.showing_records', ['from' => ':from', 'to' => ':to', 'total' => ':total']),
    ];
@endphp
<script type="application/json" id="buffaloListConfig">@json($buffaloListConfig)</script>

@push('scripts')
<script src="{{ asset('static/js/erp-listing-grid.js') }}"></script>
<script src="{{ asset('static/js/buffalo-list.js') }}"></script>
@endpush
@endsection
