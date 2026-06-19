@extends('layouts.app')
@section('title', __('reports.monthly_report'))

@section('content')

<x-section-header :title="__('reports.monthly_report')" icon="📊">
    <x-slot:actions>
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <select name="month" class="form-control" style="width:130px;" onchange="this.form.submit()">
                @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ $m==$month ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
                @endforeach
            </select>
            <select name="year" class="form-control" style="width:100px;" onchange="this.form.submit()">
                @foreach(range(now()->year, 2020) as $y)
                <option value="{{ $y }}" {{ $y==$year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </x-slot:actions>
</x-section-header>

<div class="ds-stats-grid ds-stats-grid-4">
    <x-stat-card variant="plain" icon="🥛" :label="__('reports.total_milk')" :value="number_format($totalMilk,1) . ' L'" />
    <x-stat-card variant="plain" icon="💰" :label="__('reports.total_income')" :value="'₹' . number_format($totalIncome,0)" />
    <x-stat-card variant="plain" icon="💸" :label="__('reports.total_expense')" :value="'₹' . number_format($totalExpense,0)" valueClass="text-danger" />
    <x-stat-card variant="plain" icon="📈" :label="__('reports.profit')" :value="'₹' . number_format(abs($netProfit),0)" :valueClass="$netProfit >= 0 ? '' : 'text-danger'" />
</div>

<div class="grid-2" style="margin-bottom:var(--ds-space-4);">
    <x-form-card :title="__('reports.daily_milk') . ' (L)'" icon="📅">
        <canvas id="dailyChart" height="200"></canvas>
    </x-form-card>
    <x-form-card :title="__('reports.expense_type')" icon="💸">
        @if($expenseByCategory->count())
        <canvas id="expPieChart" height="200"></canvas>
        @else
        <p class="text-muted" style="text-align:center;padding:40px 0;">{{ __('reports.no_expense') }}</p>
        @endif
    </x-form-card>
</div>

<x-form-card :title="__('reports.buffalo_production')" icon="🐃" :flush="true">
    <x-erp-listing :per-page="25" id="monthly-buffalo" :search="false" :total-meta="count($buffaloSummary) . ' total'">
        <x-slot:toolbar>
            <input type="search" id="monthlyBuffaloSearch" class="form-control form-control-sm" placeholder="{{ __('common.search_placeholder') }}" autocomplete="off">
        </x-slot:toolbar>
    <x-responsive-table>
        <table class="ds-table" id="monthlyBuffaloTable">
            <thead><tr>
                <th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th>
                <th>{{ __('reports.tag') }}</th><th>{{ __('reports.name') }}</th><th>{{ __('reports.total') }} (L)</th><th>{{ __('reports.days') }}</th><th>{{ __('reports.average') }}</th>
            </tr></thead>
            <tbody>
                @forelse($buffaloSummary as $b)
                <tr>
                    <td>0</td>
                    <td><strong>{{ $b['tag'] }}</strong></td>
                    <td>{{ $b['name'] }}</td>
                    <td><strong>{{ number_format($b['total'],1) }}</strong></td>
                    <td>{{ $b['days'] }}</td>
                    <td>{{ number_format($b['avg'],1) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; color:#9ca3af;">{{ __('reports.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
    </x-erp-listing>
</x-form-card>

<x-form-card :title="__('reports.daily_summary')" icon="📋" :flush="true">
    <x-erp-listing :per-page="25" id="monthly-daily" :search="false" :total-meta="$dailyMilk->count() . ' total'">
        <x-slot:toolbar>
            <input type="search" id="monthlyDailySearch" class="form-control form-control-sm" placeholder="{{ __('common.search_placeholder') }}" autocomplete="off">
        </x-slot:toolbar>
    <x-responsive-table>
        <table class="ds-table" id="monthlyDailyTable">
            <thead><tr>
                <th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th>
                <th>{{ __('reports.date') }}</th><th>{{ __('reports.total') }} (L)</th>
            </tr></thead>
            <tbody>
                @foreach($dailyMilk as $row)
                <tr>
                    <td>0</td>
                    <td>{{ \Carbon\Carbon::parse($row->entry_date)->format('d/m/Y (D)') }}</td>
                    <td><strong>{{ number_format($row->total,1) }}</strong></td>
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
        tableId: 'monthlyBuffaloTable',
        listingId: 'monthly-buffalo',
        searchInputId: 'monthlyBuffaloSearch',
        labels: { noRecords: @json(__('reports.no_data')) },
    });
    ErpListingGrid.initStaticTable({
        tableId: 'monthlyDailyTable',
        listingId: 'monthly-daily',
        searchInputId: 'monthlyDailySearch',
    });
});
</script>
<script>
const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();

new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($dailyMilk->map(fn($d) => \Carbon\Carbon::parse($d->entry_date)->format('d'))) !!},
        datasets: [{ label: 'L', data: {!! json_encode($dailyMilk->pluck('total')) !!}, backgroundColor: primary+'88', borderColor: primary, borderWidth: 2, borderRadius: 4 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

@if($expenseByCategory->count())
new Chart(document.getElementById('expPieChart'), {
    type: 'pie',
    data: {
        labels: {!! json_encode(
    $expenseByCategory->map(fn($e) => match($e->category) {
        'feed' => __('reports.feed'),
        'medicine' => __('reports.medicine'),
        'labour' => __('reports.labour'),
        'equipment' => __('reports.equipment'),
        'veterinary' => __('reports.veterinary'),
        default => __('reports.other'),
    })
) !!},
        datasets: [{ data: {!! json_encode($expenseByCategory->pluck('total')) !!}, backgroundColor: ['#16a34a','#ef4444','#f59e0b','#3b82f6','#8b5cf6','#6b7280'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
@endif
</script>
@endpush