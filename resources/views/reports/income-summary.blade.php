@extends('layouts.app')
@section('title', __('income.summary_report'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('income.summary_report')" icon="📊">
    <x-slot:actions>
        <a href="{{ route('income.index') }}" class="btn btn-outline btn-sm">← {{ __('income.income') }}</a>
    </x-slot:actions>
</x-section-header>

<div class="erp-panel" style="margin-bottom:16px;">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
        <div class="form-group mb-0">
            <label class="form-label">{{ __('income.from_date') }}</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('income.to_date') }}</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">{{ __('income.apply_filter') }}</button>
    </form>
</div>

<x-form-card :title="__('income.financial_summary')" icon="💰">
    <table class="ds-table ds-table-compact">
        <tbody>
            <tr><td>🥛 {{ __('income.customer_milk_income') }}</td><td class="text-end"><strong>{{ $currency }}{{ number_format($summary['customer_milk'], 2) }}</strong></td></tr>
            <tr><td>🏭 {{ __('income.dairy_income') }}</td><td class="text-end"><strong>{{ $currency }}{{ number_format($summary['dairy'], 2) }}</strong></td></tr>
            <tr><td>💩 {{ __('income.manure_sale') }}</td><td class="text-end">{{ $currency }}{{ number_format($summary['manure'], 2) }}</td></tr>
            <tr><td>🐃 {{ __('income.animal_sale') }}</td><td class="text-end">{{ $currency }}{{ number_format($summary['animal_sale'], 2) }}</td></tr>
            <tr><td>📦 {{ __('income.other_income') }}</td><td class="text-end">{{ $currency }}{{ number_format($summary['other'], 2) }}</td></tr>
            <tr style="border-top:2px solid var(--ds-border);"><td><strong>💰 {{ __('income.total_income') }}</strong></td><td class="text-end"><strong>{{ $currency }}{{ number_format($summary['total'], 2) }}</strong></td></tr>
        </tbody>
    </table>
</x-form-card>
@endsection
