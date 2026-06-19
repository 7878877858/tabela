@extends('layouts.app')
@section('title', __('milk_flow.report_reconciliation'))

@section('content')

<x-section-header :title="__('milk_flow.report_reconciliation')" icon="📋">
    <x-slot:actions>
        <a href="{{ route('milk-distribution.index') }}" class="btn btn-outline btn-sm">🥛 {{ __('milk_flow.milk_distribution') }}</a>
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
        <button type="submit" class="btn btn-primary btn-sm">{{ __('milk_flow.apply_filter') }}</button>
    </form>
</div>

<x-form-card :title="__('milk_flow.report_reconciliation')" icon="📊" :flush="true">
    <x-responsive-table>
        <table class="ds-table">
            <thead>
                <tr>
                    <th>{{ __('milk_flow.date') }}</th>
                    <th class="text-end">{{ __('milk_flow.produced') }}</th>
                    <th class="text-end">{{ __('milk_flow.distributed') }}</th>
                    <th class="text-end">{{ __('milk_flow.dairy') }}</th>
                    <th class="text-end">{{ __('milk_flow.difference') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr class="{{ !$row['is_balanced'] ? 'table-warning' : '' }}">
                    <td data-label="{{ __('milk_flow.date') }}">{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                    <td class="text-end" data-label="{{ __('milk_flow.produced') }}">{{ number_format($row['production']['total'], 2) }} L</td>
                    <td class="text-end" data-label="{{ __('milk_flow.distributed') }}">{{ number_format($row['distribution']['total'], 2) }} L</td>
                    <td class="text-end" data-label="{{ __('milk_flow.dairy') }}">{{ number_format($row['dairy']['total_liter'], 2) }} L</td>
                    <td class="text-end" data-label="{{ __('milk_flow.difference') }}">
                        @if($row['is_balanced'])
                        <span class="text-success">✓ 0</span>
                        @else
                        <span class="text-danger">{{ number_format($row['unaccounted'], 2) }} L</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted" style="padding:24px;">{{ __('common.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
</x-form-card>
@endsection
