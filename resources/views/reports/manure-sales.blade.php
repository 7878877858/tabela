@extends('layouts.app')
@section('title', __('income.manure_sale_report'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('income.manure_sale_report')" icon="📊">
    <x-slot:actions>
        <a href="{{ route('income.manure-sales.index') }}" class="btn btn-outline btn-sm">← {{ __('income.manure_sale') }}</a>
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
        <div class="form-group mb-0">
            <label class="form-label">{{ __('income.buyer_name') }}</label>
            <input type="text" name="buyer" class="form-control form-control-sm" value="{{ $buyer }}">
        </div>
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <button type="submit" class="btn btn-primary btn-sm">{{ __('income.apply_filter') }}</button>
    </form>
</div>

<div class="ds-stats-grid ds-stats-grid-2" style="margin-bottom:16px;">
    <x-stat-card variant="plain" icon="⚖️" :label="__('income.total_weight')" :value="number_format($totalWeight, 2) . ' Kg'" />
    <x-stat-card variant="plain" icon="💰" :label="__('income.total_income')" :value="$currency . number_format($total, 0)" />
</div>

<x-form-card :title="__('income.manure_sale_report')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$records" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('income.date') }}</th>
                        <th class="text-end">{{ __('income.weight_kg') }}</th>
                        <th class="text-end">{{ __('income.rate_per_kg') }}</th>
                        <th>{{ __('income.buyer_name') }}</th>
                        <th class="text-end">{{ __('income.amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                    <tr>
                        <td>{{ $row->income_date->format('d-m-Y') }}</td>
                        <td class="text-end">{{ number_format($row->weight_kg, 2) }}</td>
                        <td class="text-end">{{ number_format($row->rate_per_kg, 2) }}</td>
                        <td>{{ $row->buyer_name ?? '—' }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted" style="padding:24px;">{{ __('common.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection
