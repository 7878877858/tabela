@extends('layouts.app')
@section('title', __('farm.expenses_hub'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('farm.expenses_hub')" icon="📊">
    <x-slot:actions>
        <a href="{{ route('daily-reports.create') }}" class="btn btn-primary btn-sm">{{ __('farm.open_daily_report') }}</a>
        <a href="{{ route('reports.financial-summary') }}" class="btn btn-outline btn-sm">📈 {{ __('farm.report_financial_summary') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<form method="GET" class="erp-panel" style="margin-bottom:16px; display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
    <div class="form-group mb-0">
        <label class="form-label">{{ __('income.month') }}</label>
        <select name="month" class="form-control form-control-sm" onchange="this.form.submit()">
            @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" @selected($m == $month)>{{ \Carbon\Carbon::create()->month($m)->locale('gu')->translatedFormat('F') }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group mb-0">
        <label class="form-label">{{ __('income.year') }}</label>
        <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
            @foreach(range(now()->year, 2020) as $y)
            <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="ds-stats-grid ds-stats-grid-3" style="margin-bottom:16px;">
    <x-stat-card variant="plain" icon="💰" :label="__('farm.today_expenses')" :value="$currency . number_format($dashboard['today_expenses'], 0)" />
    <x-stat-card variant="plain" icon="🌾" :label="__('farm.month_feed_purchase')" :value="$currency . number_format($dashboard['month_feed_purchase'], 0)" />
    <x-stat-card variant="plain" icon="💡" :label="__('farm.month_utility')" :value="$currency . number_format($dashboard['month_utility'], 0)" />
    <x-stat-card variant="plain" icon="🛡️" :label="__('farm.month_insurance')" :value="$currency . number_format($dashboard['month_insurance'], 0)" />
    <x-stat-card variant="plain" icon="🏦" :label="__('farm.month_loan_emi')" :value="$currency . number_format($dashboard['month_loan_emi'], 0)" />
    <x-stat-card variant="plain" icon="📊" :label="__('farm.total_expense')" :value="$currency . number_format($summary['total'], 0)" />
</div>

<section class="farm-dash__panel" style="margin-bottom:16px;">
    <h2 class="farm-dash__panel-title">📂 {{ __('farm.expenses_hub') }}</h2>
    <div class="farm-dash__action-grid">
        <a href="{{ route('expenses.daily') }}" class="farm-dash__action">💰 {{ __('farm.daily_expenses') }}</a>
        <a href="{{ route('expenses.utility-bills.index') }}" class="farm-dash__action">💡 {{ __('farm.utility_bills') }}</a>
        <a href="{{ route('expenses.insurance.index') }}" class="farm-dash__action">🛡️ {{ __('farm.insurance') }}</a>
        <a href="{{ route('expenses.loans.index') }}" class="farm-dash__action">🏦 {{ __('farm.loans') }}</a>
        <a href="{{ route('expenses.other.index') }}" class="farm-dash__action">📋 {{ __('farm.other_expenses') }}</a>
        <a href="{{ route('feeds.index') }}" class="farm-dash__action">🌾 {{ __('common.feeds') }}</a>
    </div>
</section>

<div class="alert alert-info">
    <strong>ℹ️ {{ __('farm.daily_expense_note') }}</strong>
</div>
@endsection
