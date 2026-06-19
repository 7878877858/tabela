@extends('layouts.app')
@section('title', __('income.manure_sale'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('income.manure_sale')" icon="💩">
    <x-slot:actions>
        <a href="{{ route('income.index') }}" class="btn btn-ghost btn-sm">← {{ __('income.income') }}</a>
        <a href="{{ route('reports.manure-sales') }}" class="btn btn-outline btn-sm">📊 {{ __('income.manure_sale_report') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif

<form method="GET" class="erp-panel" style="margin-bottom:16px;">
    <div class="form-group mb-0">
        <label class="form-label">{{ __('income.date') }}</label>
        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
    </div>
</form>

<div class="alert alert-info erp-panel" style="margin-bottom:16px;">
    <strong>ℹ️ {{ __('income.management_only') }}</strong>
    <p class="mb-2 mt-1">{{ __('income.enter_via_daily_report') }}</p>
    @if($dailyReport ?? null)
        <a href="{{ route('daily-reports.edit', $dailyReport) }}" class="btn btn-primary btn-sm">{{ __('income.edit_daily_report') }}</a>
    @else
        <a href="{{ route('daily-reports.create') }}" class="btn btn-primary btn-sm">{{ __('income.open_daily_report') }}</a>
    @endif
</div>

<x-form-card :title="__('income.manure_sale') . ' — ' . \Carbon\Carbon::parse($date)->format('d-m-Y')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$sales" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('common.sr_no') }}</th>
                        <th class="text-end">{{ __('income.weight_kg') }}</th>
                        <th class="text-end">{{ __('income.rate_per_kg') }}</th>
                        <th>{{ __('income.buyer_name') }}</th>
                        <th class="text-end">{{ __('income.amount') }}</th>
                        <th>{{ __('income.source_daily_report') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $row)
                    <tr>
                        <td>{{ $sales->firstItem() + $loop->index }}</td>
                        <td class="text-end">{{ number_format($row->weight_kg, 2) }}</td>
                        <td class="text-end">{{ number_format($row->rate_per_kg, 2) }}</td>
                        <td>{{ $row->buyer_name ?? '—' }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->amount, 2) }}</td>
                        <td>
                            @if($row->daily_report_id)
                                <a href="{{ route('daily-reports.edit', $row->daily_report_id) }}" class="btn btn-ghost btn-sm">{{ __('income.edit_daily_report') }}</a>
                            @else — @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:24px;">{{ __('income.no_income') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection
