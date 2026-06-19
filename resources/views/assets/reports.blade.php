@extends('layouts.app')
@section('title', __('asset.reports'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp
<link rel="stylesheet" href="{{ asset('static/css/asset-management.css') }}">
<link rel="stylesheet" href="{{ asset('static/css/daily-report.css') }}">

<div class="am-page">
    <div class="page-header">
        <h2>📊 {{ __('asset.reports') }}</h2>
        <a href="{{ route('assets.index') }}" class="btn btn-outline btn-sm">← {{ __('asset.back') }}</a>
    </div>

    <div class="am-card">
        <form method="GET" action="{{ route('reports.assets') }}" class="am-toolbar">
            <input type="hidden" name="report" value="{{ $report }}">
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
            <select name="category" class="form-control form-control-sm">
                <option value="">{{ __('asset.all_categories') }}</option>
                @foreach(\App\Models\Asset::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control form-control-sm">
                <option value="">{{ __('asset.all_statuses') }}</option>
                @foreach(\App\Models\Asset::STATUSES as $st)
                <option value="{{ $st }}" @selected($status === $st)>{{ __('asset.' . $st) }}</option>
                @endforeach
            </select>
            <select name="asset_id" class="form-control form-control-sm">
                <option value="">{{ __('asset.all_assets') }}</option>
                @foreach($assets as $a)
                <option value="{{ $a->id }}" @selected((string)$assetId === (string)$a->id)>{{ $a->asset_code }} — {{ $a->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">{{ __('asset.apply_filters') }}</button>
        </form>

        <div class="am-report-tabs">
            @foreach(['assets' => __('asset.report_assets'), 'maintenance' => __('asset.report_maintenance'), 'expenses' => __('asset.report_expenses'), 'upcoming' => __('asset.report_upcoming')] as $key => $label)
            <a href="{{ route('reports.assets', array_merge(request()->except('report'), ['report' => $key])) }}"
               class="am-report-tab {{ $report === $key ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    @if($report === 'assets')
    <div class="am-card">
        <h3 class="am-card__title">{{ __('asset.report_assets') }}</h3>
        <x-erp-listing :per-page="25" id="report-assets" :search="false">
        <div class="am-table-wrap table-responsive mobile-card-table">
            <table class="am-table">
                <thead>
                    <tr>
                        <th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th>
                        <th>{{ __('asset.image') }}</th><th>{{ __('asset.asset_code') }}</th><th>{{ __('asset.asset_name') }}</th><th>{{ __('asset.category') }}</th><th>{{ __('asset.purchase_price') }}</th><th>{{ __('asset.current_value') }}</th><th>{{ __('asset.maintenance_cost') }}</th><th>{{ __('asset.status') }}</th>
                    </tr>
                </thead>
                <tbody id="reportAssetsBody"></tbody>
            </table>
        </div>
        </x-erp-listing>
    </div>
    @endif

    @if($report === 'maintenance')
    <div class="am-card">
        <h3 class="am-card__title">{{ __('asset.report_maintenance') }}</h3>
        <x-erp-listing :per-page="25" id="report-maint" :search="false">
        <div class="am-table-wrap table-responsive mobile-card-table">
            <table class="am-table">
                <thead>
                    <tr>
                        <th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th>
                        <th>{{ __('asset.maintenance_date') }}</th><th>{{ __('asset.asset') }}</th><th>{{ __('asset.maintenance_type') }}</th><th>{{ __('asset.cost') }}</th><th>{{ __('asset.vendor_name') }}</th><th>{{ __('asset.next_service') }}</th>
                    </tr>
                </thead>
                <tbody id="reportMaintBody"></tbody>
            </table>
        </div>
        </x-erp-listing>
    </div>
    @endif

    @if($report === 'expenses')
    <div class="am-card">
        <h3 class="am-card__title">{{ __('asset.report_expenses') }}</h3>
        <x-erp-listing :per-page="25" id="report-expense" :search="false">
        <div class="am-table-wrap table-responsive mobile-card-table">
            <table class="am-table">
                <thead>
                    <tr><th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th><th>{{ __('asset.maintenance_date') }}</th><th>{{ __('asset.expense_title') }}</th><th>{{ __('asset.asset') }}</th><th>{{ __('asset.cost') }}</th><th>{{ __('asset.reference') }}</th></tr>
                </thead>
                <tbody id="reportExpenseBody"></tbody>
            </table>
        </div>
        </x-erp-listing>
    </div>
    @endif

    @if($report === 'upcoming')
    <div class="am-card">
        <h3 class="am-card__title">{{ __('asset.report_upcoming') }}</h3>
        <x-erp-listing :per-page="25" id="report-upcoming" :search="false">
        <div class="am-table-wrap table-responsive mobile-card-table">
            <table class="am-table">
                <thead>
                    <tr><th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th><th>{{ __('asset.asset') }}</th><th>{{ __('asset.category') }}</th><th>{{ __('asset.next_service') }}</th><th>{{ __('asset.maintenance_type') }}</th><th></th></tr>
                </thead>
                <tbody id="reportUpcomingBody"></tbody>
            </table>
        </div>
        </x-erp-listing>
    </div>
    @endif
</div>

<script type="application/json" id="reportAssetsJson">@json($assetsJson)</script>
<script type="application/json" id="reportMaintJson">@json($maintenances)</script>
<script type="application/json" id="reportExpenseJson">@json($assetExpenses)</script>
<script type="application/json" id="reportUpcomingJson">@json($upcoming)</script>
@endsection

@push('scripts')
@php
    $reportGridLabels = [
        'noRecords' => __('asset.no_records'),
        'previous' => __('asset.previous'),
        'next' => __('asset.next'),
        'view' => __('asset.view'),
        'assetsWord' => __('asset.assets_word'),
        'recordsWord' => __('asset.records_word'),
        'expensesWord' => __('asset.expenses_word'),
        'servicesWord' => __('asset.services_word'),
    ];
@endphp
<script src="{{ asset('static/js/erp-listing-grid.js') }}"></script>
<script src="{{ asset('static/js/asset-data-grid.js') }}"></script>
<script>
(function () {
    const currency = @json($currency);
    const fmt = (n) => currency + Number(n || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    const gridLabels = @json($reportGridLabels);
    const L = gridLabels;

    @if($report === 'assets')
    const assets = JSON.parse(document.getElementById('reportAssetsJson')?.textContent || '[]');
    AssetDataGrid.initReportGrid({
        rows: assets.map(a => {
            const img = a.image_url
                ? `<img src="${a.image_url}" alt="" class="am-asset-thumb" loading="lazy">`
                : `<span class="am-asset-thumb am-asset-thumb--empty">🚜</span>`;
            return `
            <td data-label="{{ __('asset.image') }}">${img}</td>
            <td data-label="{{ __('asset.asset_code') }}">${a.asset_code}</td>
            <td data-label="{{ __('asset.asset_name') }}">${a.name}</td>
            <td data-label="{{ __('asset.category') }}">${a.category_label}</td>
            <td data-label="{{ __('asset.purchase_price') }}">${fmt(a.purchase_price)}</td>
            <td data-label="{{ __('asset.current_value') }}">${fmt(a.current_value)}</td>
            <td data-label="{{ __('asset.maintenance_cost') }}">${fmt(a.total_maintenance)}</td>
            <td data-label="{{ __('asset.status') }}">${a.status_label}</td>`;
        }),
        tbodyId: 'reportAssetsBody',
        paginationId: 'erp-listing-footer-report-assets',
        pageSizeId: 'erp_js_per_page_report-assets',
        totalMetaId: 'erp-listing-total-report-assets',
        label: L.assetsWord,
        labels: L,
    });
    @endif

    @if($report === 'maintenance')
    const maint = JSON.parse(document.getElementById('reportMaintJson')?.textContent || '[]');
    AssetDataGrid.initReportGrid({
        rows: maint.map(m => `
            <td data-label="{{ __('asset.maintenance_date') }}">${m.maintenance_date || '—'}</td>
            <td data-label="{{ __('asset.asset') }}">${m.asset_code} ${m.asset_name}</td>
            <td data-label="{{ __('asset.maintenance_type') }}">${m.maintenance_type}</td>
            <td data-label="{{ __('asset.cost') }}">${fmt(m.cost)}</td>
            <td data-label="{{ __('asset.vendor_name') }}">${m.vendor_name || '—'}</td>
            <td data-label="{{ __('asset.next_service') }}">${m.next_service_date || '—'}</td>`),
        tbodyId: 'reportMaintBody',
        paginationId: 'erp-listing-footer-report-maint',
        pageSizeId: 'erp_js_per_page_report-maint',
        totalMetaId: 'erp-listing-total-report-maint',
        label: L.recordsWord,
        labels: L,
    });
    @endif

    @if($report === 'expenses')
    const exp = JSON.parse(document.getElementById('reportExpenseJson')?.textContent || '[]');
    AssetDataGrid.initReportGrid({
        rows: exp.map(e => `
            <td data-label="{{ __('asset.maintenance_date') }}">${e.date}</td>
            <td data-label="{{ __('asset.expense_title') }}">${e.description}</td>
            <td data-label="{{ __('asset.asset') }}">${e.asset_name || '—'}</td>
            <td data-label="{{ __('asset.cost') }}">${fmt(e.amount)}</td>
            <td data-label="{{ __('asset.reference') }}">${e.ref}</td>`),
        tbodyId: 'reportExpenseBody',
        paginationId: 'erp-listing-footer-report-expense',
        pageSizeId: 'erp_js_per_page_report-expense',
        totalMetaId: 'erp-listing-total-report-expense',
        label: L.expensesWord,
        labels: L,
    });
    @endif

    @if($report === 'upcoming')
    const up = JSON.parse(document.getElementById('reportUpcomingJson')?.textContent || '[]');
    AssetDataGrid.initReportGrid({
        rows: up.map(u => `
            <td data-label="{{ __('asset.asset') }}">${u.asset_code} ${u.asset_name}</td>
            <td data-label="{{ __('asset.category') }}">${u.category}</td>
            <td data-label="{{ __('asset.next_service') }}">${u.next_service_date}</td>
            <td data-label="{{ __('asset.maintenance_type') }}">${u.last_type}</td>
            <td data-label="" class="mobile-card-actions erp-listing__actions"><div class="mobile-card-actions__group"><a href="${u.show_url}" class="btn btn-outline btn-sm" title="${L.view}">👁</a></div></td>`),
        tbodyId: 'reportUpcomingBody',
        paginationId: 'erp-listing-footer-report-upcoming',
        pageSizeId: 'erp_js_per_page_report-upcoming',
        totalMetaId: 'erp-listing-total-report-upcoming',
        label: L.servicesWord,
        labels: L,
    });
    @endif
})();
</script>
@endpush
