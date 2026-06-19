@extends('layouts.app')
@section('title', __('income.income'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('income.income_hub')" icon="📈">
    <x-slot:actions>
        <a href="{{ route('reports.animal-sales') }}" class="btn btn-outline btn-sm">📊 {{ __('income.animal_sale_report') }}</a>
        <a href="{{ route('income.manure-sales.index') }}" class="btn btn-outline btn-sm">💩 {{ __('income.manure_sale') }}</a>
        <a href="{{ route('income.other.index') }}" class="btn btn-outline btn-sm">📦 {{ __('income.other_income') }}</a>
        <a href="{{ route('reports.income-summary') }}" class="btn btn-primary btn-sm">📊 {{ __('income.summary_report') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
<div class="alert alert-info">{{ session('info') }}</div>
@endif

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

<div class="alert alert-info erp-panel" style="margin-bottom:16px;">
    <strong>ℹ️ {{ __('income.milk_auto_note') }}</strong>
    <p class="mb-0 mt-1 small">{{ __('income.milk_auto_detail') }}</p>
    <p class="mb-0 mt-2"><a href="{{ route('daily-reports.create') }}" class="btn btn-primary btn-sm">{{ __('income.open_daily_report') }}</a></p>
</div>

<section class="farm-dash__panel" style="margin-bottom:16px;">
    <h2 class="farm-dash__panel-title">💰 {{ __('income.financial_summary') }}</h2>
    <div class="ds-stats-grid ds-stats-grid-3">
        <x-stat-card variant="plain" icon="🥛" :label="__('income.customer_milk_income')" :value="$currency . number_format($summary['customer_milk'], 0)" />
        <x-stat-card variant="plain" icon="🏭" :label="__('income.dairy_income')" :value="$currency . number_format($summary['dairy'], 0)" />
        <x-stat-card variant="plain" icon="💩" :label="__('income.manure_sale')" :value="$currency . number_format($summary['manure'], 0)" />
        <x-stat-card variant="plain" icon="🐃" :label="__('income.animal_sale')" :value="$currency . number_format($summary['animal_sale'], 0)" />
        <x-stat-card variant="plain" icon="📦" :label="__('income.other_income')" :value="$currency . number_format($summary['other'], 0)" />
        <x-stat-card variant="plain" icon="💰" :label="__('income.total_income')" :value="$currency . number_format($summary['total'], 0)" />
    </div>
</section>

<x-form-card :title="__('income.recent_manual')" icon="📋" :flush="true">
    <x-responsive-table>
        <table class="ds-table">
            <thead>
                <tr>
                    <th>{{ __('income.date') }}</th>
                    <th>{{ __('income.category') }}</th>
                    <th>{{ __('income.description') }}</th>
                    <th class="text-end">{{ __('income.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentManual as $row)
                <tr>
                    <td>{{ $row->income_date->format('d-m-Y') }}</td>
                    <td>{{ $row->category_label }}</td>
                    <td>{{ $row->description }}</td>
                    <td class="text-end">{{ $currency }}{{ number_format($row->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">{{ __('income.no_income') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
</x-form-card>
@endsection
