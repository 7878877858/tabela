@extends('layouts.app')
@section('title', __('milk_flow.report_distribution'))

@section('content')
@include('components.milk-customer-select-assets')

@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('milk_flow.report_distribution')" icon="📊">
    <x-slot:actions>
        <a href="{{ route('milk-distribution.index') }}" class="btn btn-outline btn-sm">← {{ __('milk_flow.milk_distribution') }}</a>
    </x-slot:actions>
</x-section-header>

<div class="erp-panel" style="margin-bottom:16px;">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
        <div class="form-group mb-0">
            <label class="form-label">{{ __('milk_flow.from_date') }}</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('milk_flow.to_date') }}</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>
        <div class="form-group mb-0" style="min-width:200px;">
            <label class="form-label">{{ __('milk_flow.customer') }}</label>
            <select name="customer_id" class="form-control form-control-sm milk-customer-select">
                <option value="">{{ __('milk_flow.all_customers') }}</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected((string)$customerId === (string)$c->id)>{{ $c->display_label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('milk_flow.milk_type') }}</label>
            <select name="milk_type" class="form-control form-control-sm">
                <option value="">{{ __('milk_flow.all_types') }}</option>
                <option value="buffalo" @selected($milkType === 'buffalo')>{{ __('milk_flow.buffalo') }}</option>
                <option value="cow" @selected($milkType === 'cow')>{{ __('milk_flow.cow') }}</option>
            </select>
        </div>
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <button type="submit" class="btn btn-primary btn-sm">{{ __('milk_flow.apply_filter') }}</button>
    </form>
</div>

<div class="ds-stats-grid ds-stats-grid-2" style="margin-bottom:16px;">
    <x-stat-card variant="plain" :label="__('milk_flow.total_liter')" :value="number_format($totals->liters ?? 0, 2) . ' L'" />
    <x-stat-card variant="plain" :label="__('milk_flow.customer_income')" :value="$currency . number_format($totals->amount ?? 0, 0)" />
</div>

<x-form-card :title="__('milk_flow.report_distribution')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$records" :per-page="$perPage" :search="false" id="report-distribution">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('common.sr_no') }}</th>
                        <th>{{ __('milk_flow.date') }}</th>
                        <th>{{ __('milk_flow.customer') }}</th>
                        <th>{{ __('milk_flow.milk_type') }}</th>
                        <th class="text-end">{{ __('milk_flow.total_liter') }}</th>
                        <th class="text-end">{{ __('milk_flow.amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                    <tr>
                        <td>{{ $records->firstItem() + $loop->index }}</td>
                        <td>{{ $row->date->format('d-m-Y') }}</td>
                        <td>{{ $row->customer?->name ?? '—' }}</td>
                        <td>{{ $row->milk_type_label }}</td>
                        <td class="text-end">{{ number_format($row->total_liter, 2) }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:24px;">{{ __('common.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection
