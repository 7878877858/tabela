@extends('layouts.app')
@section('title', __('farm.report_daily_expenses'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('farm.report_daily_expenses')" icon="📊">
    <x-slot:actions>
        <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm">← {{ __('farm.expenses_hub') }}</a>
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

<div class="alert alert-info">{{ __('farm.total') }}: <strong>{{ $currency }}{{ number_format($total, 2) }}</strong></div>

<x-form-card :title="__('farm.report_daily_expenses')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$records" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('farm.date') }}</th>
                        <th>{{ __('farm.expense_category') }}</th>
                        <th class="text-end">{{ __('farm.amount') }}</th>
                        <th>{{ __('farm.remarks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                    <tr>
                        <td>{{ $row->expense_date->format('d-m-Y') }}</td>
                        <td>{{ $row->description }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->amount, 2) }}</td>
                        <td>{{ $row->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">{{ __('farm.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection
