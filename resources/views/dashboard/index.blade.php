@extends('layouts.app')
@section('title', __('dashboard.title'))

@section('content')
<link rel="stylesheet" href="{{ asset('static/css/dashboard.css') }}">

@php
    $currency = $settings['currency'];
    $score = $healthScore['score'];
    $scoreGrade = $healthScore['grade'];
    $scoreLabel = match($scoreGrade) {
        'excellent' => __('dashboard.score_excellent'),
        'good' => __('dashboard.score_good'),
        'fair' => __('dashboard.score_fair'),
        default => __('dashboard.score_critical'),
    };
    $scoreColor = match(true) {
        $score >= 85 => '#16a34a',
        $score >= 70 => '#2563eb',
        $score >= 50 => '#d97706',
        default => '#dc2626',
    };
    $animalLabels = [
        __('dashboard.buffalo'),
        __('dashboard.cow'),
        __('dashboard.buffalo_calf'),
        __('dashboard.cow_calf'),
    ];
    $animalData = [
        $animalTypeCounts['buffalo'] ?? 0,
        $animalTypeCounts['cow'] ?? 0,
        $animalTypeCounts['buffalo_calf'] ?? 0,
        $animalTypeCounts['cow_calf'] ?? 0,
    ];
@endphp

<div class="farm-dash">

    {{-- Top bar: greeting + health score --}}
    <header class="farm-dash__top">
        <div class="farm-dash__intro">
            <p class="farm-dash__eyebrow">{{ $settings['farm_name'] }} · {{ __('dashboard.farm_overview') }}</p>
            <h1 class="farm-dash__title">👋 {{ __('dashboard.welcome') }}, {{ $userName }}</h1>
            <p class="farm-dash__date">{{ __('dashboard.today_date') }}: {{ now()->translatedFormat('d F Y') }}</p>
        </div>

        <div class="farm-dash__score-card" style="--score-color: {{ $scoreColor }}">
            <div class="farm-dash__score-ring" aria-hidden="true">
                <svg viewBox="0 0 120 120">
                    <circle class="farm-dash__score-track" cx="60" cy="60" r="52"/>
                    <circle class="farm-dash__score-fill" cx="60" cy="60" r="52"
                            style="stroke-dasharray: {{ 2 * 3.14159 * 52 }}; stroke-dashoffset: {{ 2 * 3.14159 * 52 * (1 - $score / 100) }}"/>
                </svg>
                <span class="farm-dash__score-num">{{ $score }}</span>
            </div>
            <div class="farm-dash__score-meta">
                <strong>{{ __('dashboard.farm_health_score') }}</strong>
                <span class="farm-dash__score-grade">{{ $scoreLabel }}</span>
                @if($healthScore['issues'] > 0)
                <small>{{ __('dashboard.score_issues', ['count' => $healthScore['issues']]) }}</small>
                @endif
            </div>
        </div>
    </header>

    {{-- 1. Live Farm Status --}}
    <section class="farm-dash__panel farm-dash__live">
        <h2 class="farm-dash__panel-title">📡 {{ __('dashboard.live_farm_status') }}</h2>
        <div class="farm-dash__live-grid">
            <div class="farm-dash__live-item farm-dash__live-item--milk">
                <span class="farm-dash__live-val">{{ number_format($todayMilk, 1) }} L</span>
                <span class="farm-dash__live-lbl">{{ __('dashboard.today_milk') }}</span>
            </div>
            <div class="farm-dash__live-item farm-dash__live-item--stock">
                <span class="farm-dash__live-val">{{ number_format($milkStock, 1) }} L</span>
                <span class="farm-dash__live-lbl">{{ __('dashboard.milk_stock') }}</span>
            </div>
            <div class="farm-dash__live-item farm-dash__live-item--animals">
                <span class="farm-dash__live-val">{{ $totalAnimals }}</span>
                <span class="farm-dash__live-lbl">{{ __('dashboard.active_animals') }}</span>
            </div>
            <div class="farm-dash__live-item farm-dash__live-item--lactating">
                <span class="farm-dash__live-val">{{ $lactatingCount }}</span>
                <span class="farm-dash__live-lbl">{{ __('dashboard.lactating') }}</span>
            </div>
            <div class="farm-dash__live-item farm-dash__live-item--sales">
                <span class="farm-dash__live-val">{{ number_format($todaySoldLiters, 1) }} L</span>
                <span class="farm-dash__live-lbl">{{ __('dashboard.today_sales') }} · {{ $currency }}{{ number_format($todaySalesAmount, 0) }}</span>
            </div>
            <div class="farm-dash__live-item farm-dash__live-item--{{ $netOperational >= 0 ? 'profit' : 'loss' }}">
                <span class="farm-dash__live-val">{{ $currency }}{{ number_format(abs($netOperational), 0) }}</span>
                <span class="farm-dash__live-lbl">{{ __('dashboard.month_net') }} · {{ $netOperational >= 0 ? __('dashboard.profit') : __('dashboard.loss') }}</span>
            </div>
        </div>
    </section>

    {{-- Farm accounting snapshot --}}
    <section class="farm-dash__panel">
        <h2 class="farm-dash__panel-title">📊 {{ __('farm.financial_summary') }}</h2>
        <div class="ds-stats-grid ds-stats-grid-3">
            <x-stat-card variant="plain" icon="💰" :label="__('farm.today_expenses')" :value="$settings['currency'] . number_format($financialToday['today_expenses'], 0)" />
            <x-stat-card variant="plain" icon="🌾" :label="__('farm.month_feed_purchase')" :value="$settings['currency'] . number_format($financialToday['month_feed_purchase'], 0)" />
            <x-stat-card variant="plain" icon="💡" :label="__('farm.month_utility')" :value="$settings['currency'] . number_format($financialToday['month_utility'], 0)" />
            <x-stat-card variant="plain" icon="🛡️" :label="__('farm.month_insurance')" :value="$settings['currency'] . number_format($financialToday['month_insurance'], 0)" />
            <x-stat-card variant="plain" icon="🏦" :label="__('farm.month_loan_emi')" :value="$settings['currency'] . number_format($financialToday['month_loan_emi'], 0)" />
            <x-stat-card variant="plain" icon="🐃" :label="__('farm.month_animal_purchase')" :value="$settings['currency'] . number_format($financialToday['month_animal_purchase'], 0)" />
            <x-stat-card variant="plain" icon="💵" :label="__('farm.month_animal_sale')" :value="$settings['currency'] . number_format($financialToday['month_animal_sale'], 0)" />
            <x-stat-card variant="plain" icon="🔧" :label="__('farm.month_asset_purchase')" :value="$settings['currency'] . number_format($financialToday['month_asset_purchase'], 0)" />
        </div>
        <p class="mt-2 mb-0"><a href="{{ route('reports.financial-summary') }}" class="btn btn-outline btn-sm">📈 {{ __('farm.report_financial_summary') }}</a></p>
    </section>

    {{-- Milk flow: production → distribution → dairy --}}
    <section class="farm-dash__panel">
        <h2 class="farm-dash__panel-title">🥛 {{ __('milk_flow.today_summary') }}</h2>
        <div class="ds-stats-grid ds-stats-grid-3" style="margin-bottom:12px;">
            <x-stat-card variant="plain" icon="🐃" :label="__('milk_flow.buffalo') . ' ' . __('milk_flow.production')" :value="number_format($milkFlow['production']['buffalo'], 1) . ' L'" />
            <x-stat-card variant="plain" icon="🐄" :label="__('milk_flow.cow') . ' ' . __('milk_flow.production')" :value="number_format($milkFlow['production']['cow'], 1) . ' L'" />
            <x-stat-card variant="plain" icon="🥛" :label="__('milk_flow.total_production')" :value="number_format($milkFlow['production']['total'], 1) . ' L'" />
        </div>
        <div class="ds-stats-grid ds-stats-grid-3">
            <x-stat-card variant="plain" icon="🏠" :label="__('milk_flow.customer_distribution')" :value="number_format($milkFlow['distribution']['total'], 1) . ' L'" />
            <x-stat-card variant="plain" icon="🏭" :label="__('milk_flow.dairy_collection_liters')" :value="number_format($milkFlow['dairy']['total_liter'], 1) . ' L'" />
            <x-stat-card variant="plain" icon="💰" :label="__('milk_flow.total_milk_income')" :value="$currency . number_format($milkFlow['total_income'], 0)" />
        </div>
        <div class="ds-stats-grid ds-stats-grid-2" style="margin-top:12px;">
            <x-stat-card variant="plain" icon="👥" :label="__('milk_flow.customer_income')" :value="$currency . number_format($milkFlow['customer_income'], 0)" />
            <x-stat-card variant="plain" icon="🏭" :label="__('milk_flow.dairy_income')" :value="$currency . number_format($milkFlow['dairy_income'], 0)" />
        </div>
        <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('milk-distribution.index') }}" class="btn btn-outline btn-sm">🥛 {{ __('milk_flow.milk_distribution') }}</a>
            <a href="{{ route('dairy-collections.index') }}" class="btn btn-outline btn-sm">🏭 {{ __('milk_flow.dairy_collection') }}</a>
            <a href="{{ route('reports.milk-reconciliation') }}" class="btn btn-ghost btn-sm">📋 {{ __('milk_flow.report_reconciliation') }}</a>
        </div>
    </section>

    {{-- Farm income summary (month) --}}
    <section class="farm-dash__panel">
        <h2 class="farm-dash__panel-title">💰 {{ __('income.financial_summary') }} — {{ now()->locale('gu')->translatedFormat('F Y') }}</h2>
        <div class="ds-stats-grid ds-stats-grid-3">
            <x-stat-card variant="plain" icon="🥛" :label="__('income.customer_milk_income')" :value="$currency . number_format($incomeSummary['customer_milk'], 0)" />
            <x-stat-card variant="plain" icon="🏭" :label="__('income.dairy_income')" :value="$currency . number_format($incomeSummary['dairy'], 0)" />
            <x-stat-card variant="plain" icon="💩" :label="__('income.manure_sale')" :value="$currency . number_format($incomeSummary['manure'], 0)" />
            <x-stat-card variant="plain" icon="🐃" :label="__('income.animal_sale')" :value="$currency . number_format($incomeSummary['animal_sale'], 0)" />
            <x-stat-card variant="plain" icon="📦" :label="__('income.other_income')" :value="$currency . number_format($incomeSummary['other'], 0)" />
            <x-stat-card variant="plain" icon="💰" :label="__('income.total_income')" :value="$currency . number_format($incomeSummary['total'], 0)" />
        </div>
        <div class="farm-dash__panel-actions" style="margin-top:12px;">
            <a href="{{ route('income.index') }}" class="btn btn-outline btn-sm">📈 {{ __('income.income_hub') }}</a>
            <a href="{{ route('reports.income-summary') }}" class="btn btn-ghost btn-sm">📊 {{ __('income.summary_report') }}</a>
        </div>
    </section>

    {{-- 2. Farm Health Overview --}}
    <section class="farm-dash__panel farm-dash__health-strip">
        <h2 class="farm-dash__panel-title">💚 {{ __('dashboard.farm_health_overview') }}</h2>
        <div class="farm-dash__health-pills">
            <div class="farm-dash__pill farm-dash__pill--purple">
                <span class="farm-dash__pill-val">{{ $pregnantCount }}</span>
                <span class="farm-dash__pill-lbl">{{ __('dashboard.pregnant') }}</span>
            </div>
            <div class="farm-dash__pill farm-dash__pill--pink">
                <span class="farm-dash__pill-val">{{ $deliveryThisWeek }}</span>
                <span class="farm-dash__pill-lbl">{{ __('dashboard.delivery_week') }}</span>
            </div>
            <div class="farm-dash__pill farm-dash__pill--orange">
                <span class="farm-dash__pill-val">{{ $heatReminders }}</span>
                <span class="farm-dash__pill-lbl">{{ __('dashboard.in_heat') }}</span>
            </div>
            <div class="farm-dash__pill farm-dash__pill--{{ $lowFeedStock->count() > 0 ? 'danger' : 'ok' }}">
                <span class="farm-dash__pill-val">{{ $lowFeedStock->count() }}</span>
                <span class="farm-dash__pill-lbl">{{ __('dashboard.low_feed') }}</span>
            </div>
            <div class="farm-dash__pill farm-dash__pill--{{ $pendingSalary > 0 ? 'warning' : 'ok' }}">
                <span class="farm-dash__pill-val">{{ $pendingSalary > 0 ? $currency . number_format($pendingSalary, 0) : '—' }}</span>
                <span class="farm-dash__pill-lbl">{{ __('dashboard.pending_salary') }}</span>
            </div>
            <div class="farm-dash__pill farm-dash__pill--{{ $upcomingAssetServices > 0 ? 'info' : 'ok' }}">
                <span class="farm-dash__pill-val">{{ $upcomingAssetServices }}</span>
                <span class="farm-dash__pill-lbl">{{ __('dashboard.asset_service') }}</span>
            </div>
        </div>
    </section>

    {{-- Charts row --}}
    <div class="farm-dash__charts">
        {{-- 3. Milk Production Chart --}}
        <section class="farm-dash__panel farm-dash__chart-panel">
            <h2 class="farm-dash__panel-title">📈 {{ __('dashboard.milk_production_chart') }}</h2>
            <div class="farm-dash__chart-wrap">
                @if($last7->sum('liters') > 0)
                <canvas id="milkChart" height="160"></canvas>
                @else
                <p class="farm-dash__empty">{{ __('dashboard.no_milk_data') }}</p>
                @endif
            </div>
        </section>

        {{-- 4. Animal Distribution Chart --}}
        <section class="farm-dash__panel farm-dash__chart-panel">
            <h2 class="farm-dash__panel-title">🐄 {{ __('dashboard.animal_distribution') }}</h2>
            <div class="farm-dash__chart-wrap farm-dash__chart-wrap--donut">
                @if($totalAnimals > 0)
                <canvas id="animalChart" height="160"></canvas>
                <div class="farm-dash__donut-center">
                    <strong>{{ $totalAnimals }}</strong>
                    <small>{{ __('dashboard.active_animals') }}</small>
                </div>
                @else
                <p class="farm-dash__empty">{{ __('dashboard.no_animals') }}</p>
                @endif
            </div>
        </section>
    </div>

    {{-- Bottom row: alerts, top producers, quick actions --}}
    <div class="farm-dash__bottom">

        {{-- 5. Today's Alerts --}}
        <section class="farm-dash__panel farm-dash__alerts">
            <h2 class="farm-dash__panel-title">🔔 {{ __('dashboard.todays_alerts') }}</h2>
            <ul class="farm-dash__alert-list">
                @foreach($alerts as $alert)
                <li class="farm-dash__alert farm-dash__alert--{{ $alert['level'] }}">
                    <span class="farm-dash__alert-icon">{{ $alert['icon'] }}</span>
                    @if($alert['route'])
                    <a href="{{ $alert['route'] }}" class="farm-dash__alert-text">{{ $alert['message'] }}</a>
                    @else
                    <span class="farm-dash__alert-text">{{ $alert['message'] }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
        </section>

        {{-- 6. Top Milk Producing Animals --}}
        <section class="farm-dash__panel farm-dash__producers">
            <h2 class="farm-dash__panel-title">🏆 {{ __('dashboard.top_milk_animals') }}</h2>
            <p class="farm-dash__panel-sub">{{ __('dashboard.this_month') }} · {{ number_format($monthMilk, 1) }} L {{ __('dashboard.liters') }}</p>
            @if($topProducers->isNotEmpty())
            <ul class="farm-dash__producer-list">
                @foreach($topProducers as $i => $b)
                @php $pct = $maxProducerLiters > 0 ? round(($b['total'] / $maxProducerLiters) * 100) : 0; @endphp
                <li class="farm-dash__producer">
                    <span class="farm-dash__producer-rank">{{ $i + 1 }}</span>
                    <div class="farm-dash__producer-body">
                        <div class="farm-dash__producer-head">
                            <strong>{{ $b['tag'] }}</strong>
                            @if($b['name'] !== $b['tag'])<span class="farm-dash__producer-name">{{ $b['name'] }}</span>@endif
                            <span class="farm-dash__producer-liters">{{ number_format($b['total'], 1) }} L</span>
                        </div>
                        <div class="farm-dash__producer-bar"><span style="width: {{ $pct }}%"></span></div>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <p class="farm-dash__empty">{{ __('dashboard.no_producers') }}</p>
            @endif
        </section>

        {{-- 7. Quick Actions --}}
        <section class="farm-dash__panel farm-dash__actions">
            <h2 class="farm-dash__panel-title">⚡ {{ __('dashboard.quick_actions') }}</h2>
            <div class="farm-dash__action-grid">
                <a href="{{ route('daily-reports.create') }}" class="farm-dash__action farm-dash__action--primary">
                    <span>📋</span>{{ __('dashboard.action_daily_report') }}
                </a>
                <a href="{{ route('sale.index') }}" class="farm-dash__action">
                    <span>🥛</span>{{ __('dashboard.action_milk_sale') }}
                </a>
                <a href="{{ route('expenses.index') }}" class="farm-dash__action">
                    <span>💸</span>{{ __('farm.expenses_hub') }}
                </a>
                <a href="{{ route('income.index') }}" class="farm-dash__action">
                    <span>💰</span>{{ __('dashboard.action_income') }}
                </a>
                <a href="{{ route('buffalo.index') }}" class="farm-dash__action">
                    <span>🐃</span>{{ __('dashboard.action_animals') }}
                </a>
                <a href="{{ route('feeds.index') }}" class="farm-dash__action">
                    <span>🌾</span>{{ __('dashboard.action_feeds') }}
                </a>
                <a href="{{ route('tasks.index') }}" class="farm-dash__action">
                    <span>✅</span>{{ __('dashboard.action_tasks') }}
                </a>
                <a href="{{ route('reports.monthly') }}" class="farm-dash__action">
                    <span>📊</span>{{ __('dashboard.action_monthly') }}
                </a>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#1d4ed8';

    @if($last7->sum('liters') > 0)
    new Chart(document.getElementById('milkChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($last7->pluck('date')) !!},
            datasets: [{
                label: 'L',
                data: {!! json_encode($last7->pluck('liters')) !!},
                borderColor: primary,
                backgroundColor: primary + '22',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: primary,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
    @endif

    @if($totalAnimals > 0)
    new Chart(document.getElementById('animalChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($animalLabels) !!},
            datasets: [{
                data: {!! json_encode($animalData) !!},
                backgroundColor: ['#3b82f6', '#22c55e', '#93c5fd', '#86efac'],
                borderWidth: 0,
                spacing: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 10, boxWidth: 12 }
                }
            }
        }
    });
    @endif
})();
</script>
@endpush
