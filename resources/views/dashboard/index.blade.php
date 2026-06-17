@extends('layouts.app')
@section('title', __('dashboard.title'))

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

<div class="erp-dashboard">

    @if(!$todayMilkEntered)
    <div class="alert alert-error erp-dashboard__alert">
        ⚠️ આજનો દૂધ ડેટા બાકી છે — <a href="{{ route('daily-reports.create') }}"><strong>દૈનિક અહેવાલ દાખલ કરો</strong></a>
    </div>
    @endif

    {{-- Welcome header --}}
    <header class="erp-dashboard__hero erp-dashboard__section erp-dashboard__section--hero">
        <h1 class="erp-dashboard__hero-title">👋 Welcome, {{ $userName }}</h1>
        <p class="erp-dashboard__hero-date">Today: {{ now()->format('d F Y') }}</p>
        <div class="erp-dashboard__hero-stats">
            <span class="erp-dashboard__hero-stat">🥛 Milk Produced Today: <strong>{{ number_format($todayMilk, 1) }} L</strong></span>
            <span class="erp-dashboard__hero-stat">🐄 Current Animals: <strong>{{ $totalBuffaloes }}</strong></span>
            <span class="erp-dashboard__hero-stat">🥛 {{ __('dashboard.lactating') }}: <strong>{{ $lactatingCount }}</strong></span>
        </div>
    </header>

    {{-- Primary KPIs --}}
    <div class="erp-kpi-grid erp-kpi-grid--5 erp-dashboard__section erp-dashboard__section--primary">
        <x-dashboard-kpi icon="🐃" accent="blue" :value="$totalBuffaloes" :label="__('dashboard.total_buffaloes')" :sub="$animalTypeSummary" />
        <x-dashboard-kpi icon="🐄" accent="cow" :value="$animalTypeCounts['cow'] ?? 0" label="Cows" :sub="'ભેંસ બચ્ચું ' . ($animalTypeCounts['buffalo_calf'] ?? 0)" />
        <x-dashboard-kpi icon="🥛" accent="purple" :value="number_format($todayMilk, 1) . ' L'" :label="__('dashboard.today_milk')" :sub="now()->format('d/m/Y')" />
        <x-dashboard-kpi icon="💰" accent="green" :value="$settings['currency'] . number_format($monthIncome, 0)" :label="__('dashboard.month_income')" :sub="__('dashboard.this_month')" />
        <x-dashboard-kpi icon="💸" accent="red" :value="$settings['currency'] . number_format($monthExpense, 0)" :label="__('dashboard.month_expense')" :sub="__('dashboard.this_month')" />
    </div>

    {{-- Quick actions (high priority on mobile) --}}
    <div class="erp-panel erp-dashboard__section erp-dashboard__section--actions">
        <h2 class="erp-panel__title">⚡ {{ __('dashboard.quick_action') }}</h2>
        <p class="erp-panel__subtitle">Frequently used farm operations</p>
        <div class="erp-actions-grid">
            <a href="{{ route('daily-reports.create') }}" class="erp-action-tile">
                <span class="erp-action-tile__icon">➕</span>
                <span>Daily Report</span>
            </a>
            <a href="{{ route('sale.index') }}" class="erp-action-tile">
                <span class="erp-action-tile__icon">🥛</span>
                <span>Milk Sale</span>
            </a>
            <a href="{{ route('income.index') }}" class="erp-action-tile">
                <span class="erp-action-tile__icon">💰</span>
                <span>Income</span>
            </a>
            <a href="{{ route('kharch.index') }}" class="erp-action-tile">
                <span class="erp-action-tile__icon">💸</span>
                <span>Expense</span>
            </a>
            <a href="{{ route('reports.monthly') }}" class="erp-action-tile">
                <span class="erp-action-tile__icon">📊</span>
                <span>Monthly Report</span>
            </a>
            <a href="{{ route('reports.yearly') }}" class="erp-action-tile">
                <span class="erp-action-tile__icon">📅</span>
                <span>Yearly Report</span>
            </a>
        </div>
    </div>

    {{-- Secondary KPIs --}}
    <div class="erp-kpi-grid erp-kpi-grid--4 erp-dashboard__section erp-dashboard__section--secondary">
        <x-dashboard-kpi icon="📅" accent="purple" :value="number_format($monthMilk, 1) . ' L'" :label="__('dashboard.month_milk')" :sub="now()->format('F Y')" />
        <x-dashboard-kpi
            :icon="$netProfit >= 0 ? '📈' : '📉'"
            :accent="$netProfit >= 0 ? 'green' : 'red'"
            :value="$settings['currency'] . number_format(abs($netProfit), 0)"
            :label="$netProfit >= 0 ? __('dashboard.profit') : __('dashboard.loss')"
            :sub="__('dashboard.this_month')"
        />
        <x-dashboard-kpi icon="🤰" accent="orange" :value="$pregnantCount" label="Pregnant Animals" :sub="$deliveryThisWeek . ' due this week'" />
        <x-dashboard-kpi icon="🫙" accent="teal" :value="number_format($remainingMilk, 1) . ' L'" label="Remaining Milk" sub="Today's stock" />
    </div>

    {{-- Charts --}}
    <div class="erp-chart-grid erp-dashboard__section erp-dashboard__section--charts">
        <div class="erp-panel">
            <h2 class="erp-panel__title">{{ __('dashboard.last_7_days_milk') }}</h2>
            <p class="erp-panel__subtitle">Milk trend for recent week</p>
            <div class="erp-panel__chart-wrap" id="milkChartWrap">
                @if($last7->sum('liters') > 0)
                <canvas id="milkChart" height="180"></canvas>
                @else
                <div class="erp-panel__empty">No milk records available for the last 7 days</div>
                @endif
            </div>
        </div>
        <div class="erp-panel">
            <h2 class="erp-panel__title">Animal Distribution</h2>
            <p class="erp-panel__subtitle">Current animal population</p>
            <div class="erp-panel__chart-wrap" id="animalChartWrap">
                @if($totalBuffaloes > 0)
                <canvas id="animalChart" height="180"></canvas>
                @else
                <div class="erp-panel__empty">No animals registered yet</div>
                @endif
            </div>
        </div>
    </div>

    <div class="erp-panel erp-dashboard__section erp-dashboard__section--expense">
        <h2 class="erp-panel__title">{{ __('dashboard.expense_by_type') }}</h2>
        <p class="erp-panel__subtitle">Monthly expense breakdown</p>
        <div class="erp-panel__chart-wrap" id="expenseChartWrap">
            @if($expenseBreakdown->isNotEmpty())
            <canvas id="expenseChart" height="180"></canvas>
            @else
            <div class="erp-panel__empty">No expenses recorded this month</div>
            @endif
        </div>
    </div>

    {{-- Top producers --}}
    <div class="erp-panel erp-dashboard__section erp-dashboard__section--ranking">
        <h2 class="erp-panel__title">🏆 {{ __('dashboard.top_milk_producers') }}</h2>
        <p class="erp-panel__subtitle">Highest milk yield this month</p>
        @if($topBuffaloes->isNotEmpty())
        <ul class="erp-rank-list">
            @foreach($topBuffaloes->values() as $i => $b)
            @php
                $medals = ['🥇', '🥈', '🥉'];
                $medal = $medals[$i] ?? null;
            @endphp
            <li class="erp-rank-item">
                <span class="erp-rank-item__medal">
                    @if($medal){{ $medal }}@else<span class="erp-rank-item__num">#{{ $i + 1 }}</span>@endif
                </span>
                <span class="erp-rank-item__name">{{ $b['tag'] }}@if($b['name'] !== $b['tag']) — {{ $b['name'] }}@endif</span>
                <span class="erp-rank-item__dots" aria-hidden="true"></span>
                <span class="erp-rank-item__val">{{ number_format($b['total'], 1) }} L</span>
            </li>
            @endforeach
        </ul>
        @else
        <div class="erp-panel__empty">No milk production data this month</div>
        @endif
    </div>

    {{-- Tertiary stats --}}
    <div class="erp-kpi-grid erp-kpi-grid--3 erp-dashboard__section erp-dashboard__section--tertiary">
        <x-dashboard-kpi icon="🥛" accent="purple" :value="$settings['currency'] . number_format($todaySalesAmount, 0)" label="Today's Sales" :sub="number_format($todaySoldLiters, 1) . ' L sold'" />
        <x-dashboard-kpi icon="⏳" accent="amber" :value="$settings['currency'] . number_format($pendingSalary, 0)" :label="__('dashboard.pending_salary')" />
        <x-dashboard-kpi icon="⚠️" accent="red" :value="$lowFeedStock->count()" label="Low Feed Stock" :sub="$heatReminders . ' in heat'" />
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#1D4ED8';

    @if($last7->sum('liters') > 0)
    new Chart(document.getElementById('milkChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($last7->pluck('date')) !!},
            datasets: [{
                label: 'Liters',
                data: {!! json_encode($last7->pluck('liters')) !!},
                backgroundColor: 'rgba(139, 92, 246, 0.35)',
                borderColor: '#8b5cf6',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
    @endif

    @if($totalBuffaloes > 0)
    new Chart(document.getElementById('animalChart'), {
        type: 'doughnut',
        data: {
            labels: ['ભેંસ', 'ગાય', 'ભેંસ બચ્ચું', 'ગાય બચ્ચું'],
            datasets: [{
                data: {!! json_encode([
                    $animalTypeCounts['buffalo'] ?? 0,
                    $animalTypeCounts['cow'] ?? 0,
                    $animalTypeCounts['buffalo_calf'] ?? 0,
                    $animalTypeCounts['cow_calf'] ?? 0,
                ]) !!},
                backgroundColor: ['#3b82f6', '#22c55e', '#60a5fa', '#4ade80'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } }
            }
        }
    });
    @endif

    @if($expenseBreakdown->isNotEmpty())
    const expLabels = {!! json_encode($expenseBreakdown->map(fn($e) => match($e->category){
        'feed' => __('dashboard.feed'),
        'medicine' => __('dashboard.medicine'),
        'labour' => __('dashboard.labour'),
        'equipment' => __('dashboard.equipment'),
        'veterinary' => __('dashboard.veterinary'),
        default => __('dashboard.other')
    })) !!};
    new Chart(document.getElementById('expenseChart'), {
        type: 'doughnut',
        data: {
            labels: expLabels,
            datasets: [{
                data: {!! json_encode($expenseBreakdown->pluck('total')) !!},
                backgroundColor: ['#ef4444', '#f97316', '#f59e0b', '#3b82f6', '#8b5cf6', '#6b7280'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } } }
        }
    });
    @endif
})();
</script>
@endpush
