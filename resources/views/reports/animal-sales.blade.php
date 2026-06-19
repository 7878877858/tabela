@extends('layouts.app')
@section('title', __('income.animal_sale_report'))

@section('content')
<x-animal-select-assets />
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('income.animal_sale_report')" icon="📊">
    <x-slot:actions>
        <a href="{{ route('animal-transactions.index') }}" class="btn btn-ghost btn-sm">← {{ __('farm.transaction_history') }}</a>
        <a href="{{ route('daily-reports.create') }}" class="btn btn-outline btn-sm">{{ __('income.open_daily_report') }}</a>
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
        <div class="form-group mb-0" style="min-width:220px;">
            <label class="form-label">{{ __('income.animal') }}</label>
            <x-animal-select name="buffalo_id" :animals="$animals" :value="$buffaloId" class="form-control-sm" />
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('income.buyer_name') }}</label>
            <input type="text" name="buyer" class="form-control form-control-sm" value="{{ $buyer }}">
        </div>
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <button type="submit" class="btn btn-primary btn-sm">{{ __('income.apply_filter') }}</button>
    </form>
</div>

<x-stat-card variant="plain" icon="💰" :label="__('income.total_income')" :value="$currency . number_format($total, 0)" style="margin-bottom:16px;" />

<x-form-card :title="__('income.animal_sale_report')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$records" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('income.date') }}</th>
                        <th>{{ __('income.animal') }}</th>
                        <th>{{ __('income.buyer_name') }}</th>
                        <th class="text-end">{{ __('income.amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                    <tr>
                        <td>{{ $row->income_date->format('d-m-Y') }}</td>
                        <td>{{ $row->buffalo?->display_label ?? '—' }}</td>
                        <td>{{ $row->buyer_name ?? '—' }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">{{ __('common.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection
