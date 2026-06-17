@extends('layouts.app')
@section('title', __('sale.milk_sales'))

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/daily-report.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/milk-ledger.css') }}">

<x-section-header :title="__('sale.milk_sales')" icon="💰">
    <x-slot:actions>
        <a href="{{ route('milk.transactions') }}" class="btn btn-outline btn-sm">📒 Milk Transactions</a>
    </x-slot:actions>
</x-section-header>

@if($errors->any())
<div class="alert alert-danger">
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<div class="summary-row">
    <span>🥛 દૂધ સ્ટોક: <strong>{{ number_format($milkBalance, 2) }} L</strong></span>
</div>

<x-form-card :title="__('sale.new_sale')" icon="➕">
    <form method="POST" action="{{ route('sale.store') }}">
        @csrf
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">{{ __('sale.date') }} *</label>
                <input type="date" name="sale_date" class="form-control" value="{{ today()->toDateString() }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('sale.liters') }} *</label>
                <input type="number" step="0.1" min="0.1" name="liters_sold" id="liters" class="form-control" placeholder="0.0" required oninput="calcSale()">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('sale.price_per_liter') }} (₹) *</label>
                <input type="number" step="0.01" name="price_per_liter" id="price" class="form-control"
                    value="{{ \App\Models\Setting::get('milk_price',55) }}" required oninput="calcSale()">
            </div>
        </div>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">{{ __('sale.buyer_name') }}</label>
                <input type="text" name="buyer_name" class="form-control" placeholder="{{ __('sale.optional') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('sale.payment') }}</label>
                <select name="payment_status" class="form-control">
                    <option value="paid">✅ {{ __('sale.paid') }}</option>
                    <option value="pending">⏳ {{ __('sale.pending') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('sale.total') }}</label>
                <div id="sale-total" style="font-size:24px; font-weight:700; color:var(--primary); padding-top:4px;">₹0</div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">➕ {{ __('sale.add') }}</button>
    </form>
</x-form-card>

<div id="milkSalesLedger" class="milk-ledger-page daily-report-page">
    <div class="ml-summary-cards">
        <div class="ml-metric-card">
            <span class="ml-metric-card__label">🥛 Filtered Liters Sold</span>
            <span class="ml-metric-card__value" id="mlSummaryTotalLiters">0 L</span>
        </div>
        <div class="ml-metric-card">
            <span class="ml-metric-card__label">💰 Filtered Sales</span>
            <span class="ml-metric-card__value" id="mlSummaryTotalSales">₹0</span>
        </div>
        <div class="ml-metric-card">
            <span class="ml-metric-card__label">⏳ Pending</span>
            <span class="ml-metric-card__value" id="mlSummaryPending">₹0</span>
        </div>
    </div>

    <x-form-card :title="__('sale.milk_sales')" icon="📋" :flush="true">
        <div class="ml-ledger-panel">
            <div class="ml-filter-bar">
                <div class="ml-filter-row">
                    <div class="form-group">
                        <label class="form-label">Date Range</label>
                        <select id="mlDatePreset" class="form-control form-control-sm">
                            <option value="today">Today</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month" selected>This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">From</label>
                        <input type="date" id="mlDateFrom" class="form-control form-control-sm" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="date" id="mlDateTo" class="form-control form-control-sm" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment</label>
                        <select id="mlPaymentStatus" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="ml-filter-row">
                    <div class="form-group">
                        <label class="form-label">Liters Min</label>
                        <input type="number" step="0.1" id="mlLitersMin" class="form-control form-control-sm" placeholder="Min">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Liters Max</label>
                        <input type="number" step="0.1" id="mlLitersMax" class="form-control form-control-sm" placeholder="Max">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount Min (₹)</label>
                        <input type="number" step="1" id="mlAmountMin" class="form-control form-control-sm" placeholder="Min">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount Max (₹)</label>
                        <input type="number" step="1" id="mlAmountMax" class="form-control form-control-sm" placeholder="Max">
                    </div>
                    <div class="form-group ml-search-group">
                        <label class="form-label">Search Buyer</label>
                        <input type="search" id="mlSearch" class="form-control form-control-sm" placeholder="Buyer name..." autocomplete="off">
                    </div>
                </div>
                <div class="ml-filter-actions">
                    <span class="text-muted" id="mlFilteredCount">0 records</span>
                    <div class="ml-export-btns">
                        <button type="button" class="btn btn-outline btn-sm" id="mlExportCsv">Export CSV</button>
                        <button type="button" class="btn btn-outline btn-sm" id="mlExportExcel">Export Excel</button>
                        <button type="button" class="btn btn-outline btn-sm" id="mlExportPdf">Export PDF</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive ml-table-wrap">
                <table class="ds-table ml-ledger-table">
                    <thead>
                        <tr>
                            <th data-sort="date">Date ↕</th>
                            <th data-sort="liters">Liters ↕</th>
                            <th data-sort="price_per_liter">Rate ↕</th>
                            <th data-sort="amount">Amount ↕</th>
                            <th data-sort="buyer_name">Buyer ↕</th>
                            <th data-sort="payment_status">Payment</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="milkSalesBody"></tbody>
                </table>
            </div>
            <div id="milkSalesPagination" class="dr-grid-pagination"></div>
        </div>
    </x-form-card>
</div>

@php
    $milkSalesConfig = [
        'csrf' => csrf_token(),
        'deleteConfirm' => __('sale.delete_confirm'),
    ];
@endphp
<script type="application/json" id="milkSalesJson">@json($salesJson)</script>
<script type="application/json" id="milkSalesConfig">@json($milkSalesConfig)</script>

@push('scripts')
<script>
function calcSale() {
    const l = parseFloat(document.getElementById('liters').value) || 0;
    const p = parseFloat(document.getElementById('price').value) || 0;
    document.getElementById('sale-total').textContent = '₹' + Math.round(l * p).toLocaleString('en-IN');
}
calcSale();
</script>
<script src="{{ asset('assets/js/milk-data-grid.js') }}"></script>
@endpush
@endsection
