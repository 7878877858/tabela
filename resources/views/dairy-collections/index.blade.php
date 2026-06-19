@extends('layouts.app')
@section('title', __('milk_flow.dairy_collection'))

@section('content')

@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('milk_flow.dairy_collection')" icon="🏭">
    <x-slot:actions>
        <a href="{{ route('milk-distribution.index', ['date' => $date]) }}" class="btn btn-outline btn-sm">🥛 {{ __('milk_flow.milk_distribution') }}</a>
        <a href="{{ route('reports.dairy-collection') }}" class="btn btn-outline btn-sm">📊 {{ __('milk_flow.report_dairy') }}</a>
        <a href="{{ route('reports.milk-reconciliation') }}" class="btn btn-primary btn-sm">📋 {{ __('milk_flow.report_reconciliation') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
<div class="alert alert-info">{{ session('info') }}</div>
@endif

@if(session('reconciliation_warning'))
@php $w = session('reconciliation_warning'); @endphp
<div class="alert alert-warning">
    <strong>⚠️ {{ __('milk_flow.reconciliation_error') }}</strong>
    <div style="margin-top:8px;">
        <div><strong>{{ __('milk_flow.buffalo') }}:</strong>
            {{ __('milk_flow.expected') }}: {{ number_format($w['expected_buffalo'], 2) }} L ·
            {{ __('milk_flow.entered') }}: {{ number_format($w['entered_buffalo'], 2) }} L ·
            {{ __('milk_flow.difference') }}: {{ number_format($w['buffalo_diff'], 2) }} L
        </div>
        <div><strong>{{ __('milk_flow.cow') }}:</strong>
            {{ __('milk_flow.expected') }}: {{ number_format($w['expected_cow'], 2) }} L ·
            {{ __('milk_flow.entered') }}: {{ number_format($w['entered_cow'], 2) }} L ·
            {{ __('milk_flow.difference') }}: {{ number_format($w['cow_diff'], 2) }} L
        </div>
    </div>
</div>
@endif

<form method="GET" class="erp-panel" style="margin-bottom:16px; display:flex; gap:12px; align-items:end;">
    <div class="form-group mb-0">
        <label class="form-label">{{ __('milk_flow.date') }}</label>
        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
    </div>
</form>

<div class="ds-stats-grid ds-stats-grid-3" style="margin-bottom:16px;">
    <x-stat-card variant="plain" icon="🐃" :label="__('milk_flow.remaining') . ' (' . __('milk_flow.buffalo') . ')'" :value="number_format($summary['remaining']['buffalo'], 2) . ' L'" />
    <x-stat-card variant="plain" icon="🐄" :label="__('milk_flow.remaining') . ' (' . __('milk_flow.cow') . ')'" :value="number_format($summary['remaining']['cow'], 2) . ' L'" />
    <x-stat-card variant="plain" icon="💰" :label="__('milk_flow.dairy_income')" :value="$currency . number_format($summary['dairy_income'], 0)" />
</div>

@if(!$summary['is_balanced'] && $summary['production']['total'] > 0)
<div class="alert alert-warning" style="margin-bottom:16px;">
    ⚠️ {{ __('milk_flow.reconciliation_error') }} — {{ __('milk_flow.unaccounted_alert', ['liters' => number_format(abs($summary['unaccounted']), 1)]) }}
</div>
@endif

<div class="alert alert-info erp-panel" style="margin-bottom:16px;">
    <strong>ℹ️ {{ __('milk_flow.management_only') }}</strong>
    <p class="mb-2 mt-1">{{ __('milk_flow.enter_via_daily_report') }}</p>
    <p class="text-muted small mb-2">લીટર ઑટો ગણાશે: ઉત્પાદન − ગ્રાહક વિતરણ</p>
    @if($dailyReport ?? null)
        <a href="{{ route('daily-reports.edit', $dailyReport) }}" class="btn btn-primary btn-sm">{{ __('milk_flow.edit_daily_report') }}</a>
    @else
        <a href="{{ route('daily-reports.create') }}" class="btn btn-primary btn-sm">{{ __('milk_flow.open_daily_report') }}</a>
    @endif
</div>

<x-form-card :title="__('milk_flow.dairy_collection') . ' — ' . \Carbon\Carbon::parse($date)->format('d-m-Y')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$collections" :per-page="$perPage" :search="false" id="dairy-collections">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('common.sr_no') }}</th>
                        <th>{{ __('milk_flow.slip_number') }}</th>
                        <th class="text-end">🐃 L</th>
                        <th class="text-end">🐄 L</th>
                        <th class="text-end">{{ __('milk_flow.amount') }}</th>
                        <th>{{ __('milk_flow.source_daily_report') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collections as $row)
                    <tr>
                        <td>{{ $collections->firstItem() + $loop->index }}</td>
                        <td>{{ $row->slip_number ?? '—' }}</td>
                        <td class="text-end">{{ number_format($row->buffalo_liter, 2) }}</td>
                        <td class="text-end">{{ number_format($row->cow_liter, 2) }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->total_amount, 2) }}</td>
                        <td>
                            @if($row->daily_report_id)
                                <a href="{{ route('daily-reports.edit', $row->daily_report_id) }}" class="btn btn-ghost btn-sm">{{ __('milk_flow.edit_daily_report') }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($row->slip_image)
                            <a href="{{ asset('storage/' . $row->slip_image) }}" target="_blank" class="btn btn-ghost btn-sm">{{ __('milk_flow.view_slip') }}</a>
                            @endif
                            <form method="POST" action="{{ route('dairy-collections.destroy', $row) }}" class="d-inline" onsubmit="return confirm('ડિલીટ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                            </form>
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
