@extends('layouts.app')
@section('title', __('farm.report_financial_summary'))

@section('content')
@php
    $currency = \App\Models\Setting::get('currency', '₹');
    $income = $summary['income'];
    $expense = $summary['expense'];
    $net = $summary['net_profit'];
@endphp

<x-section-header :title="__('farm.report_financial_summary')" icon="📈">
    <x-slot:actions>
        <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm">← {{ __('farm.expenses_hub') }}</a>
        <a href="{{ route('income.index') }}" class="btn btn-outline btn-sm">📈 {{ __('income.income_hub') }}</a>
    </x-slot:actions>
</x-section-header>

<form method="GET" class="erp-panel" style="margin-bottom:16px; display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
    <div class="form-group mb-0">
        <label class="form-label">{{ __('farm.date_from') }}</label>
        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
    </div>
    <div class="form-group mb-0">
        <label class="form-label">{{ __('farm.date_to') }}</label>
        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">{{ __('farm.filter') }}</button>
</form>

<section class="farm-dash__panel" style="margin-bottom:16px;">
    <h2 class="farm-dash__panel-title">📈 {{ __('farm.income_section') }}</h2>
    <div class="ds-stats-grid ds-stats-grid-3">
        <x-stat-card variant="plain" icon="🥛" :label="__('farm.customer_milk_income')" :value="$currency . number_format($income['customer_milk'], 0)" />
        <x-stat-card variant="plain" icon="🏭" :label="__('farm.dairy_income')" :value="$currency . number_format($income['dairy'], 0)" />
        <x-stat-card variant="plain" icon="🐃" :label="__('farm.animal_sale_income')" :value="$currency . number_format($income['animal_sale'], 0)" />
        <x-stat-card variant="plain" icon="💩" :label="__('farm.manure_sale_income')" :value="$currency . number_format($income['manure'], 0)" />
        <x-stat-card variant="plain" icon="📦" :label="__('farm.other_income')" :value="$currency . number_format($income['other'], 0)" />
        <x-stat-card variant="plain" icon="💰" :label="__('farm.total_income')" :value="$currency . number_format($income['total'], 0)" />
    </div>
</section>

<section class="farm-dash__panel" style="margin-bottom:16px;">
    <h2 class="farm-dash__panel-title">📉 {{ __('farm.expense_section') }}</h2>
    <div class="ds-stats-grid ds-stats-grid-3">
        <x-stat-card variant="plain" icon="💊" :label="__('farm.daily_expenses_total')" :value="$currency . number_format($expense['daily'], 0)" />
        <x-stat-card variant="plain" icon="🌾" :label="__('farm.feed_purchases')" :value="$currency . number_format($expense['feed'], 0)" />
        <x-stat-card variant="plain" icon="💡" :label="__('farm.utility_bills_total')" :value="$currency . number_format($expense['utility'], 0)" />
        <x-stat-card variant="plain" icon="🛡️" :label="__('farm.insurance_total')" :value="$currency . number_format($expense['insurance'], 0)" />
        <x-stat-card variant="plain" icon="🏦" :label="__('farm.loan_emi')" :value="$currency . number_format($expense['loanEmi'], 0)" />
        <x-stat-card variant="plain" icon="🐃" :label="__('farm.animal_purchase_cost')" :value="$currency . number_format($expense['animalPurchase'], 0)" />
        <x-stat-card variant="plain" icon="🔧" :label="__('farm.asset_purchases')" :value="$currency . number_format($expense['assets'] + $expense['equipment'], 0)" />
        <x-stat-card variant="plain" icon="📋" :label="__('farm.other_expenses')" :value="$currency . number_format($expense['other'], 0)" />
        <x-stat-card variant="plain" icon="📊" :label="__('farm.total_expense')" :value="$currency . number_format($expense['total'], 0)" />
    </div>
</section>

<section class="farm-dash__panel">
    <h2 class="farm-dash__panel-title">⚖️ {{ __('farm.profit_loss') }}</h2>
    <div class="alert {{ $net >= 0 ? 'alert-success' : 'alert-danger' }}" style="font-size:1.25rem;">
        {{ $currency }}{{ number_format($income['total'], 2) }}
        <strong>−</strong>
        {{ $currency }}{{ number_format($expense['total'], 2) }}
        <strong>=</strong>
        <strong>{{ $net >= 0 ? __('farm.net_profit') : __('farm.net_loss') }}: {{ $currency }}{{ number_format(abs($net), 2) }}</strong>
    </div>
</section>
@endsection
