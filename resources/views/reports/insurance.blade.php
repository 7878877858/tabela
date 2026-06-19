@extends('layouts.app')
@section('title', __('farm.report_insurance'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp
@include('reports.partials.date-filter')
<x-form-card :title="__('farm.report_insurance')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$records" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead><tr><th>{{ __('farm.insurance_type') }}</th><th>{{ __('farm.policy_number') }}</th><th class="text-end">{{ __('farm.premium_amount') }}</th><th>{{ __('farm.expiry_date') }}</th></tr></thead>
                <tbody>
                    @forelse($records as $row)
                    <tr>
                        <td>{{ __('farm.' . $row->insurance_type . '_insurance') }}</td>
                        <td>{{ $row->policy_number ?? '—' }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->premium_amount, 2) }}</td>
                        <td>{{ $row->expiry_date->format('d-m-Y') }}</td>
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
