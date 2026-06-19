@extends('layouts.app')
@section('title', __('farm.report_loan'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp
@include('reports.partials.date-filter')
<x-form-card :title="__('farm.report_loan')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$records" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead><tr><th>{{ __('farm.loan_name') }}</th><th>{{ __('farm.bank_name') }}</th><th class="text-end">{{ __('farm.loan_amount') }}</th><th class="text-end">{{ __('farm.emi_amount') }}</th></tr></thead>
                <tbody>
                    @forelse($records as $row)
                    <tr>
                        <td>{{ $row->loan_name }}</td>
                        <td>{{ $row->bank_name ?? '—' }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->loan_amount, 2) }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->emi_amount, 2) }}</td>
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
