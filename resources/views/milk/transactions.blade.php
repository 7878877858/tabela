@extends('layouts.app')
@section('title', 'Milk Transactions')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/daily-report.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/milk-ledger.css') }}">

<x-section-header title="Milk Transactions" icon="📒">
    <x-slot:actions>
        <a href="{{ route('sale.index') }}" class="btn btn-outline btn-sm">💰 Milk Sales</a>
    </x-slot:actions>
</x-section-header>

<div class="summary-row">
    <span>🥛 દૂધ સ્ટોક: <strong>{{ number_format($milkBalance, 2) }} L</strong></span>
</div>

<div id="milkTxnLedger" class="milk-ledger-page daily-report-page">
    <div class="ml-summary-cards ml-summary-cards--4">
        <div class="ml-metric-card">
            <span class="ml-metric-card__label">🥛 Total Production</span>
            <span class="ml-metric-card__value" id="mlTxnSummaryMilk">0 L</span>
        </div>
        <div class="ml-metric-card">
            <span class="ml-metric-card__label">💰 Total Sales</span>
            <span class="ml-metric-card__value" id="mlTxnSummarySales">0 L</span>
        </div>
        <div class="ml-metric-card">
            <span class="ml-metric-card__label">📉 Adjustments</span>
            <span class="ml-metric-card__value" id="mlTxnSummaryAdjust">0 L</span>
        </div>
        <div class="ml-metric-card">
            <span class="ml-metric-card__label">📊 Net Production</span>
            <span class="ml-metric-card__value" id="mlTxnSummaryNet">0 L</span>
        </div>
    </div>

    <x-form-card title="Milk Transactions Ledger" icon="📒" :flush="true">
        <div class="ml-ledger-panel">
            <div class="ml-filter-bar">
                <div class="ml-filter-row">
                    <div class="form-group">
                        <label class="form-label">Date Range</label>
                        <select id="mlTxnDatePreset" class="form-control form-control-sm">
                            <option value="today">Today</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month" selected>This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">From</label>
                        <input type="date" id="mlTxnDateFrom" class="form-control form-control-sm" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="date" id="mlTxnDateTo" class="form-control form-control-sm" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Transaction Type</label>
                        <select id="mlTxnType" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="production">Production</option>
                            <option value="adjust">Adjustment</option>
                            <option value="wastage">Wastage</option>
                            <option value="sale">Sale</option>
                        </select>
                    </div>
                </div>
                <div class="ml-filter-row">
                    <div class="form-group">
                        <label class="form-label">Animal Type</label>
                        <select id="mlTxnAnimalType" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="buffalo">Buffalo</option>
                            <option value="cow">Cow</option>
                            <option value="buffalo_calf">Buffalo Calf</option>
                            <option value="cow_calf">Cow Calf</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Animal</label>
                        <select id="mlTxnAnimal" class="form-control form-control-sm">
                            <option value="">All Animals</option>
                            @foreach($animalsJson as $animal)
                            <option value="{{ $animal['id'] }}">{{ $animal['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group ml-search-group">
                        <label class="form-label">Search</label>
                        <input type="search" id="mlTxnSearch" class="form-control form-control-sm" placeholder="Tag / Name / Buyer..." autocomplete="off">
                    </div>
                </div>
                <div class="ml-filter-actions">
                    <span class="text-muted" id="mlTxnFilteredCount">0 records</span>
                    <div class="ml-export-btns">
                        <button type="button" class="btn btn-outline btn-sm" id="mlTxnExportCsv">Export CSV</button>
                        <button type="button" class="btn btn-outline btn-sm" id="mlTxnExportExcel">Export Excel</button>
                        <button type="button" class="btn btn-outline btn-sm" id="mlTxnExportPdf">Export PDF</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive ml-table-wrap">
                <table class="ds-table ml-ledger-table">
                    <thead>
                        <tr>
                            <th data-sort="date">Date ↕</th>
                            <th data-sort="type_label">Type ↕</th>
                            <th>In/Out</th>
                            <th data-sort="liters">Liters ↕</th>
                            <th data-sort="balance_after">Balance ↕</th>
                            <th data-sort="animal_label">Animal ↕</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="milkTxnBody"></tbody>
                </table>
            </div>
            <div id="milkTxnPagination" class="dr-grid-pagination"></div>
        </div>
    </x-form-card>
</div>

<script type="application/json" id="milkTxnJson">@json($transactionsJson)</script>

@push('scripts')
<script src="{{ asset('assets/js/milk-data-grid.js') }}"></script>
@endpush
@endsection
