@extends('layouts.app')
@section('title', __('farm.report_feed_purchase'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp
<x-section-header :title="__('farm.report_feed_purchase')" icon="📊">
    <x-slot:actions>
        <a href="{{ route('feeds.index') }}" class="btn btn-ghost btn-sm">← {{ __('common.feeds') }}</a>
    </x-slot:actions>
</x-section-header>
@include('reports.partials.date-filter')
<x-form-card :title="__('farm.report_feed_purchase')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$records" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead><tr><th>{{ __('farm.date') }}</th><th>{{ __('farm.feed_type') }}</th><th class="text-end">{{ __('farm.amount') }}</th><th>{{ __('farm.supplier') }}</th></tr></thead>
                <tbody>
                    @forelse($records as $row)
                    <tr>
                        <td>{{ $row->purchase_date->format('d-m-Y') }}</td>
                        <td>{{ __('farm.' . $row->feed_type) }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($row->amount, 2) }}</td>
                        <td>{{ $row->supplier ?? '—' }}</td>
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
