@extends('layouts.app')
@section('title', __('milk_flow.milk_distribution'))

@section('content')

@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('milk_flow.milk_distribution')" icon="🥛">
    <x-slot:actions>
        <a href="{{ route('milk-customers.index') }}" class="btn btn-outline btn-sm">{{ __('milk_flow.manage_customers') }}</a>
        <a href="{{ route('reports.milk-distribution') }}" class="btn btn-outline btn-sm">📊 {{ __('milk_flow.report_distribution') }}</a>
        <a href="{{ route('dairy-collections.index', ['date' => $date]) }}" class="btn btn-primary btn-sm">🏭 {{ __('milk_flow.dairy_collection') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
<div class="alert alert-info">{{ session('info') }}</div>
@endif

<form method="GET" class="erp-panel no-print" style="margin-bottom:16px; display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
    <div class="form-group mb-0">
        <label class="form-label">{{ __('milk_flow.date') }}</label>
        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
    </div>
</form>

<div class="ds-stats-grid ds-stats-grid-4" style="margin-bottom:16px;">
    <x-stat-card variant="plain" icon="🐃" :label="__('milk_flow.buffalo') . ' ' . __('milk_flow.production')" :value="number_format($summary['production']['buffalo'], 1) . ' L'" />
    <x-stat-card variant="plain" icon="🐄" :label="__('milk_flow.cow') . ' ' . __('milk_flow.production')" :value="number_format($summary['production']['cow'], 1) . ' L'" />
    <x-stat-card variant="plain" icon="🥛" :label="__('milk_flow.total_production')" :value="number_format($summary['production']['total'], 1) . ' L'" />
    <x-stat-card variant="plain" icon="💰" :label="__('milk_flow.customer_income')" :value="$currency . number_format($summary['customer_income'], 0)" />
</div>

<div class="grid-2" style="gap:16px; margin-bottom:16px;">
    <x-form-card :title="__('milk_flow.buffalo')" icon="🐃">
        <table class="ds-table ds-table-compact">
            <tr><td>{{ __('milk_flow.production') }}</td><td class="text-end"><strong>{{ number_format($summary['production']['buffalo'], 2) }} L</strong></td></tr>
            <tr><td>{{ __('milk_flow.distributed') }}</td><td class="text-end">{{ number_format($summary['distribution']['buffalo'], 2) }} L</td></tr>
            <tr><td>{{ __('milk_flow.remaining') }}</td><td class="text-end text-primary"><strong>{{ number_format($summary['remaining']['buffalo'], 2) }} L</strong></td></tr>
            <tr><td>{{ __('milk_flow.dairy') }}</td><td class="text-end">{{ number_format($summary['dairy']['buffalo_liter'], 2) }} L</td></tr>
            @if(abs($summary['buffalo_diff']) >= 0.01)
            <tr class="text-danger"><td>{{ __('milk_flow.difference') }}</td><td class="text-end">{{ number_format($summary['buffalo_diff'], 2) }} L</td></tr>
            @endif
        </table>
    </x-form-card>
    <x-form-card :title="__('milk_flow.cow')" icon="🐄">
        <table class="ds-table ds-table-compact">
            <tr><td>{{ __('milk_flow.production') }}</td><td class="text-end"><strong>{{ number_format($summary['production']['cow'], 2) }} L</strong></td></tr>
            <tr><td>{{ __('milk_flow.distributed') }}</td><td class="text-end">{{ number_format($summary['distribution']['cow'], 2) }} L</td></tr>
            <tr><td>{{ __('milk_flow.remaining') }}</td><td class="text-end text-primary"><strong>{{ number_format($summary['remaining']['cow'], 2) }} L</strong></td></tr>
            <tr><td>{{ __('milk_flow.dairy') }}</td><td class="text-end">{{ number_format($summary['dairy']['cow_liter'], 2) }} L</td></tr>
            @if(abs($summary['cow_diff']) >= 0.01)
            <tr class="text-danger"><td>{{ __('milk_flow.difference') }}</td><td class="text-end">{{ number_format($summary['cow_diff'], 2) }} L</td></tr>
            @endif
        </table>
    </x-form-card>
</div>

@if(!$summary['is_balanced'] && $summary['production']['total'] > 0)
<div class="alert alert-warning">
    ⚠️ {{ __('milk_flow.reconciliation_error') }} — {{ __('milk_flow.unaccounted_alert', ['liters' => number_format(abs($summary['unaccounted']), 1)]) }}
</div>
@endif

<div class="alert alert-info erp-panel" style="margin-bottom:16px;">
    <strong>ℹ️ {{ __('milk_flow.management_only') }}</strong>
    <p class="mb-2 mt-1">{{ __('milk_flow.enter_via_daily_report') }}</p>
    @if($dailyReport ?? null)
        <a href="{{ route('daily-reports.edit', $dailyReport) }}" class="btn btn-primary btn-sm">{{ __('milk_flow.edit_daily_report') }}</a>
    @else
        <a href="{{ route('daily-reports.create') }}" class="btn btn-primary btn-sm">{{ __('milk_flow.open_daily_report') }}</a>
    @endif
</div>

<x-form-card :title="__('milk_flow.milk_distribution') . ' — ' . \Carbon\Carbon::parse($date)->format('d-m-Y')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$distributions" :per-page="$perPage" :search="false" id="milk-distribution">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('common.sr_no') }}</th>
                        <th>{{ __('milk_flow.customer') }}</th>
                        <th>{{ __('milk_flow.milk_type') }}</th>
                        <th class="text-end">{{ __('milk_flow.morning_liter') }}</th>
                        <th class="text-end">{{ __('milk_flow.evening_liter') }}</th>
                        <th class="text-end">{{ __('milk_flow.total_liter') }}</th>
                        <th class="text-end">{{ __('milk_flow.amount') }}</th>
                        <th>{{ __('milk_flow.source_daily_report') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distributions as $row)
                    <tr>
                        <td data-label="{{ __('common.sr_no') }}">{{ $distributions->firstItem() + $loop->index }}</td>
                        <td data-label="{{ __('milk_flow.customer') }}">{{ $row->customer?->name ?? '—' }}</td>
                        <td data-label="{{ __('milk_flow.milk_type') }}">{{ $row->milk_type_label }}</td>
                        <td class="text-end" data-label="{{ __('milk_flow.morning_liter') }}">{{ number_format($row->morning_liter, 2) }}</td>
                        <td class="text-end" data-label="{{ __('milk_flow.evening_liter') }}">{{ number_format($row->evening_liter, 2) }}</td>
                        <td class="text-end" data-label="{{ __('milk_flow.total_liter') }}"><strong>{{ number_format($row->total_liter, 2) }}</strong></td>
                        <td class="text-end" data-label="{{ __('milk_flow.amount') }}">{{ $currency }}{{ number_format($row->amount, 2) }}</td>
                        <td data-label="{{ __('milk_flow.source_daily_report') }}">
                            @if($row->daily_report_id)
                                <a href="{{ route('daily-reports.edit', $row->daily_report_id) }}" class="btn btn-ghost btn-sm">{{ __('milk_flow.edit_daily_report') }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('milk-distribution.destroy', $row) }}" onsubmit="return confirm('ડિલીટ કરવું?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted" style="padding:24px;">{{ __('common.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>

@push('scripts')
@endpush
@endsection
