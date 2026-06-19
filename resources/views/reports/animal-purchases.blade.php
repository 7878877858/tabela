@extends('layouts.app')
@section('title', __('farm.report_animal_purchase'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp
@include('reports.partials.date-filter')
<x-form-card :title="__('farm.report_animal_purchase')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$records" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead><tr><th>{{ __('farm.date') }}</th><th>{{ __('farm.animal') }}</th><th>{{ __('farm.seller') }}</th><th class="text-end">{{ __('farm.purchase_price') }}</th></tr></thead>
                <tbody>
                    @forelse($records as $row)
                    <tr>
                        <td>{{ $row->purchase_date?->format('d-m-Y') ?? '—' }}</td>
                        <td>{{ $row->display_label }}</td>
                        <td>—</td>
                        <td class="text-end">{{ $currency }}{{ number_format((float) $row->purchase_price, 2) }}</td>
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
