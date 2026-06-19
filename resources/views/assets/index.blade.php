@extends('layouts.app')
@section('title', __('asset.assets_management'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp
<link rel="stylesheet" href="{{ asset('static/css/asset-management.css') }}">
<link rel="stylesheet" href="{{ asset('static/css/daily-report.css') }}">

<div class="am-page">
    <div class="am-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h2>🚜 {{ __('asset.assets_management') }}</h2>
                <p>{{ __('asset.hero_subtitle') }}</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('assets.create') }}" class="btn btn-light btn-sm">➕ {{ __('asset.add_asset') }}</a>
                <a href="{{ route('reports.assets') }}" class="btn btn-outline-light btn-sm">📊 {{ __('asset.reports') }}</a>
            </div>
        </div>
    </div>

    <div class="am-summary-grid">
        <div class="am-metric">
            <span class="am-metric__label">🚜 {{ __('asset.total_assets') }}</span>
            <span class="am-metric__value">{{ $summary['total_assets'] }}</span>
        </div>
        <div class="am-metric">
            <span class="am-metric__label">💰 {{ __('asset.total_asset_value') }}</span>
            <span class="am-metric__value">{{ $currency }}{{ number_format($summary['total_value'], 0) }}</span>
        </div>
        <div class="am-metric">
            <span class="am-metric__label">🔧 {{ __('asset.month_maintenance') }}</span>
            <span class="am-metric__value">{{ $currency }}{{ number_format($summary['month_maintenance'], 0) }}</span>
        </div>
        <div class="am-metric">
            <span class="am-metric__label">⚠️ {{ __('asset.upcoming_services') }}</span>
            <span class="am-metric__value">{{ $summary['upcoming_services'] }}</span>
        </div>
    </div>

    <div class="am-card">
        <h3 class="am-card__title">📦 {{ __('asset.asset_list') }}</h3>

        <x-erp-listing :per-page="25" id="assets-list" :search="false">
            <x-slot:toolbar>
                <input type="search" id="assetSearch" class="form-control form-control-sm" placeholder="{{ __('asset.search_placeholder') }}" autocomplete="off">
                <select id="assetCategoryFilter" class="form-control form-control-sm">
                    <option value="">{{ __('asset.all_categories') }}</option>
                    @foreach($categories as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select id="assetStatusFilter" class="form-control form-control-sm">
                    <option value="">{{ __('asset.all_statuses') }}</option>
                    @foreach($statuses as $st)
                    <option value="{{ $st }}">{{ __('asset.' . $st) }}</option>
                    @endforeach
                </select>
            </x-slot:toolbar>

            <div class="am-table-wrap table-responsive mobile-card-table">
                <table class="am-table" id="assetGridTable">
                    <thead>
                        <tr>
                            <th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th>
                            <th>Image</th>
                            <th data-asset-sort="asset_code">Asset Code</th>
                            <th data-asset-sort="name">Asset Name</th>
                            <th data-asset-sort="category">Category</th>
                            <th data-asset-sort="purchase_price">Purchase Price</th>
                            <th data-asset-sort="current_value">Current Value</th>
                            <th data-asset-sort="total_maintenance">Maintenance</th>
                            <th data-asset-sort="next_service_date">Next Service</th>
                            <th data-asset-sort="status">Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="assetGridBody"></tbody>
                </table>
            </div>
        </x-erp-listing>
    </div>
</div>

<script type="application/json" id="assetsJson">@json($assetsJson)</script>
@endsection

@push('scripts')
@php
    $assetGridLabels = [
        'noAssetsFound' => __('asset.no_assets_found'),
        'deleteConfirm' => __('asset.delete_asset_confirm'),
        'previous' => __('asset.previous'),
        'next' => __('asset.next'),
        'assetsWord' => __('asset.assets_word'),
        'view' => __('asset.view'),
        'edit' => __('asset.edit'),
        'srNoLabel' => __('common.sr_no'),
    ];
@endphp
<script src="{{ asset('static/js/erp-listing-grid.js') }}"></script>
<script src="{{ asset('static/js/asset-data-grid.js') }}"></script>
<script>
(function () {
    let rows = [];
    try { rows = JSON.parse(document.getElementById('assetsJson')?.textContent || '[]'); } catch (e) {}
    AssetDataGrid.initAssetGrid({
        rows,
        tbodyId: 'assetGridBody',
        paginationId: 'erp-listing-footer-assets-list',
        searchId: 'assetSearch',
        categoryId: 'assetCategoryFilter',
        statusId: 'assetStatusFilter',
        pageSizeId: 'erp_js_per_page_assets-list',
        totalMetaId: 'erp-listing-total-assets-list',
        listingId: 'assets-list',
        currency: @json($currency),
        csrf: @json(csrf_token()),
        destroyBase: @json(url('assets')),
        noImageLabel: @json(__('asset.no_image')),
        labels: @json($assetGridLabels),
    });
})();
</script>
@endpush
