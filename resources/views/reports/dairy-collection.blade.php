@extends('layouts.app')
@section('title', __('milk_flow.report_dairy'))

@section('content')

@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('milk_flow.report_dairy')" icon="📊">
    <x-slot:actions>
        <a href="{{ route('dairy-collections.index') }}" class="btn btn-outline btn-sm">← {{ __('milk_flow.dairy_collection') }}</a>
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
        <div class="form-group mb-0">
            <label class="form-label">{{ __('milk_flow.slip_number') }}</label>
            <input type="search" name="slip_number" class="form-control form-control-sm" value="{{ $slipNumber }}">
        </div>
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <button type="submit" class="btn btn-primary btn-sm">{{ __('milk_flow.apply_filter') }}</button>
    </form>
</div>

<div class="ds-stats-grid ds-stats-grid-3" style="margin-bottom:16px;">
    <x-stat-card variant="plain" icon="🐃" :label="__('milk_flow.buffalo')" :value="number_format($totals->buffalo_liter ?? 0, 2) . ' L'" />
    <x-stat-card variant="plain" icon="🐄" :label="__('milk_flow.cow')" :value="number_format($totals->cow_liter ?? 0, 2) . ' L'" />
    <x-stat-card variant="plain" icon="💰" :label="__('milk_flow.dairy_income')" :value="$currency . number_format($totals->amount ?? 0, 0)" />
</div>

<x-form-card :title="__('milk_flow.report_dairy')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$records" :per-page="$perPage" :search="false" id="report-dairy">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('common.sr_no') }}</th>
                        <th>{{ __('milk_flow.date') }}</th>
                        <th>{{ __('milk_flow.slip_number') }}</th>
                        <th class="text-end">🐃 L</th>
                        <th class="text-end">🐄 L</th>
                        <th class="text-end">{{ __('milk_flow.amount') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                    <tr>
                        <td>{{ $records->firstItem() + $loop->index }}</td>
                        <td>{{ $row->date->format('d-m-Y') }}</td>
                        <td>{{ $row->slip_number ?? '—' }}</td>
                        <td class="text-end">{{ number_format($row->buffalo_liter, 2) }}</td>
                        <td class="text-end">{{ number_format($row->cow_liter, 2) }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->total_amount, 2) }}</td>
                        <td>
                            @if($row->slip_image)
                            <a href="{{ asset('storage/' . $row->slip_image) }}" target="_blank" class="btn btn-ghost btn-sm">{{ __('milk_flow.view_slip') }}</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted" style="padding:24px;">{{ __('common.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection
