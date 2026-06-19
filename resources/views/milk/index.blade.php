@extends('layouts.app')
@section('title', __('milk.milk_entry'))

@section('content')

<x-section-header :title="__('milk.milk_entry')" icon="🥛">
    <x-slot:actions>
        <a href="{{ route('milk.history') }}" class="btn btn-outline btn-sm">📋 {{ __('milk.milk_history') }}</a>
        <a href="{{ route('daily-reports.create') }}" class="btn btn-primary btn-sm">📋 દૈનિક અહેવાલ</a>
    </x-slot:actions>
</x-section-header>

<div class="alert alert-success">
    💡 મુખ્ય દૂધ એન્ટ્રી માટે <a href="{{ route('daily-reports.create') }}"><strong>દૈનિક અહેવાલ</strong></a> વાપરો — એક જ જગ્યાએ દૂધ + ચારો + સ્ટાફ.
</div>

<x-form-card title="Date & Summary" icon="📅">
    <form method="GET" class="d-flex flex-wrap align-items-center gap-2">
        <div class="form-group" style="margin:0;min-width:180px;">
            <label class="form-label">{{ __('milk.date') }}</label>
            <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
        </div>
        <div style="font-size:0.875rem;color:var(--ds-text-muted);padding-top:1.5rem;">
            {{ __('milk.total') }}: <strong>{{ __('milk.morning') }} {{ number_format($totalMorning,1) }}L + {{ __('milk.evening') }} {{ number_format($totalEvening,1) }}L = {{ number_format($totalLiters,1) }}L</strong>
        </div>
    </form>
</x-form-card>

<div class="alert alert-warning">
    📋 <strong>Read-only view</strong> — synced from Daily Report. <a href="{{ route('daily-reports.create') }}">Enter data in Daily Report</a>
</div>

<x-form-card :title="__('milk.milk_entry')" icon="🥛" :flush="true">
    <x-erp-listing :per-page="25" id="milk-entry" :search="false" :total-meta="__('common.total_records', ['total' => $buffaloes->count()])">
        <x-slot:toolbar>
            <input type="search" id="milkEntrySearch" class="form-control form-control-sm" placeholder="{{ __('common.search_placeholder') }}" autocomplete="off">
        </x-slot:toolbar>
    <x-responsive-table>
        <table class="ds-table" id="milkEntryTable">
            <thead>
                <tr>
                    <th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th>
                    <th>{{ __('milk.tag_name') }}</th>
                    <th>{{ __('milk.morning_liters') }} (L)</th>
                    <th>{{ __('milk.evening_liters') }} (L)</th>
                    <th>{{ __('milk.total_milk') }} (L)</th>
                    <th>{{ __('milk.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($buffaloes as $buffalo)
                @php $entry = $entries[$buffalo->id] ?? null; @endphp
                <tr>
                    <td>0</td>
                    <td data-label="{{ __('milk.tag_name') }}">
                        <strong>{{ $buffalo->tag_number }}</strong>
                        @if($buffalo->name) <span class="text-muted" style="font-size:12px;">{{ $buffalo->name }}</span> @endif
                    </td>
                    <td data-label="{{ __('milk.morning_liters') }}">{{ $entry ? number_format($entry->morning_liters, 2) : '—' }}</td>
                    <td data-label="{{ __('milk.evening_liters') }}">{{ $entry ? number_format($entry->evening_liters, 2) : '—' }}</td>
                    <td class="text-primary" style="font-weight:600;" data-label="{{ __('milk.total_milk') }}">{{ $entry ? number_format($entry->total_liters, 1) : '—' }}</td>
                    <td data-label="{{ __('milk.notes') }}">{{ $entry?->notes ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center" style="padding:2rem;color:#94a3b8;">{{ __('milk.no_active_buffalo') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection

@push('scripts')
<script src="{{ asset('static/js/erp-listing-grid.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    ErpListingGrid.initStaticTable({
        tableId: 'milkEntryTable',
        listingId: 'milk-entry',
        searchInputId: 'milkEntrySearch',
        labels: { noRecords: @json(__('milk.no_active_buffalo')) },
    });
});
</script>
@endpush
