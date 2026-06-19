@extends('layouts.app')
@section('title', __('reports.yearly_report'))

@section('content')

<x-section-header :title="__('reports.yearly_report')" icon="📈">
    <x-slot:actions>
        <form method="GET">
            <select name="year" class="form-control" style="width:100px;" onchange="this.form.submit()">
                @foreach(range(now()->year, 2020) as $y)
                <option value="{{ $y }}" {{ $y==$year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </x-slot:actions>
</x-section-header>

<div class="ds-stats-grid ds-stats-grid-4">
    <x-stat-card variant="plain" icon="🥛" :label="$year . ' ' . __('reports.total_milk_year')" :value="number_format($totalMilk,0) . ' L'" />
    <x-stat-card variant="plain" icon="💰" :label="__('reports.total_income')" :value="'₹' . number_format($totalIncome,0)" />
    <x-stat-card variant="plain" icon="💸" :label="__('reports.total_expense')" :value="'₹' . number_format($totalExpense,0)" valueClass="text-danger" />
    <x-stat-card variant="plain" :icon="$totalProfit >= 0 ? '📈' : '📉'" :label="$totalProfit >= 0 ? __('reports.profit') : __('reports.loss')" :value="'₹' . number_format(abs($totalProfit),0)" :valueClass="$totalProfit >= 0 ? '' : 'text-danger'" />
</div>

<x-form-card :title="__('reports.monthly_chart')" icon="📊">
    <canvas id="yearChart" height="120"></canvas>
</x-form-card>

<x-form-card :title="__('reports.yearly_report')" icon="📋" :flush="true" style="margin-top:var(--ds-space-4);">
    <x-erp-listing :per-page="25" id="yearly-monthly" :search="false" :total-meta="count($monthly) . ' total'">
        <x-slot:toolbar>
            <input type="search" id="yearlyMonthlySearch" class="form-control form-control-sm" placeholder="{{ __('common.search_placeholder') }}" autocomplete="off">
        </x-slot:toolbar>
    <x-responsive-table>
        <table class="ds-table" id="yearlyMonthlyTable">
            <thead><tr>
                <th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th>
                <th>{{ __('reports.month') }}</th><th>{{ __('reports.milk') }} (L)</th><th>{{ __('reports.income') }} (₹)</th><th>{{ __('reports.expense') }} (₹)</th><th>{{ __('reports.profit_loss') }}</th>
            </tr></thead>
            <tbody>
                @foreach($monthly as $row)
                @php
                $months = ['', __('reports.jan'), __('reports.feb'), __('reports.mar'), __('reports.apr'), __('reports.may'), __('reports.jun'), __('reports.jul'), __('reports.aug'), __('reports.sep'), __('reports.oct'), __('reports.nov'), __('reports.dec')];
                @endphp
                <tr>
                    <td>0</td>
                    <td><strong>{{ $months[$row['month']] }}</strong></td>
                    <td>{{ number_format($row['milk'],1) }}</td>
                    <td>₹{{ number_format($row['income'],0) }}</td>
                    <td>₹{{ number_format($row['expense'],0) }}</td>
                    <td class="{{ $row['profit'] >= 0 ? 'text-primary' : 'text-danger' }}" style="font-weight:600;">
                        {{ $row['profit'] >= 0 ? '+' : '' }}₹{{ number_format($row['profit'],0) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection

@push('scripts')
<script src="{{ asset('static/js/erp-listing-grid.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    ErpListingGrid.initStaticTable({
        tableId: 'yearlyMonthlyTable',
        listingId: 'yearly-monthly',
        searchInputId: 'yearlyMonthlySearch',
    });
});
</script>
<script>
const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#1D4ED8';
const labels = [
    '{{ __("reports.jan") }}','{{ __("reports.feb") }}','{{ __("reports.mar") }}','{{ __("reports.apr") }}',
    '{{ __("reports.may") }}','{{ __("reports.jun") }}','{{ __("reports.jul") }}','{{ __("reports.aug") }}',
    '{{ __("reports.sep") }}','{{ __("reports.oct") }}','{{ __("reports.nov") }}','{{ __("reports.dec") }}'
];
const income  = {!! json_encode(collect($monthly)->pluck('income')) !!};
const expense = {!! json_encode(collect($monthly)->pluck('expense')) !!};

new Chart(document.getElementById('yearChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: '{{ __('reports.income') }}', data: income, backgroundColor: primary+'bb', borderRadius: 4 },
            { label: '{{ __('reports.expense') }}', data: expense, backgroundColor: '#ef444488', borderRadius: 4 }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: { legend: { position: 'top' } }
    }
});
</script>
@endpush
