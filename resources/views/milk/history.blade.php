@extends('layouts.app')
@section('title', __('milk.milk_history'))

@section('content')

<x-section-header :title="__('milk.milk_history')" icon="📋">
    <x-slot:actions>
        <a href="{{ route('milk.index') }}" class="btn btn-outline btn-sm">🥛 {{ __('milk.milk_entry') }}</a>
    </x-slot:actions>
</x-section-header>

<x-form-card :title="__('milk.milk_history')" icon="🔍">
    <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
        
        <label>{{ __('milk.month') }}:</label>
        <select name="month" onchange="this.form.submit()" class="form-control" style="width:120px;">
            @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                    {{ $m }}
                </option>
            @endfor
        </select>

        <label>{{ __('milk.year') }}:</label>
        <input type="number" name="year" value="{{ $year }}" 
            class="form-control" style="width:100px;" onchange="this.form.submit()">

        <span style="margin-left:auto; font-size:14px;">
            {{ __('milk.total') }} {{ __('milk.milk') }}: 
            <strong style="color:var(--primary);">
                {{ number_format($monthTotal,1) }} L
            </strong>
        </span>

    </form>
</x-form-card>

<x-form-card :title="__('milk.milk_history')" icon="📋" :flush="true">
    <x-erp-listing :per-page="25" id="milk-history" :search="false" :total-meta="count($daily) . ' total'">
        <x-slot:toolbar>
            <input type="search" id="milkHistorySearch" class="form-control form-control-sm" placeholder="{{ __('common.search_placeholder') }}" autocomplete="off">
        </x-slot:toolbar>
    <x-responsive-table>
        <table class="ds-table" id="milkHistoryTable">
            <thead>
                <tr>
                    <th class="erp-listing__sr-col">{{ __('common.sr_no') }}</th>
                    <th>{{ __('milk.date') }}</th>
                    <th>{{ __('milk.morning') }} (L)</th>
                    <th>{{ __('milk.evening') }} (L)</th>
                    <th>{{ __('milk.total') }} (L)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daily as $row)
                <tr>
                    <td>0</td>
                    <td>{{ $row->entry_date }}</td>
                    <td>{{ number_format($row->morning,1) }}</td>
                    <td>{{ number_format($row->evening,1) }}</td>
                    <td style="font-weight:600; color:var(--primary);">
                        {{ number_format($row->total,1) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:30px; color:#9ca3af;">
                        {{ __('milk.no_data') }}
                    </td>
                </tr>
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
        tableId: 'milkHistoryTable',
        listingId: 'milk-history',
        searchInputId: 'milkHistorySearch',
        labels: { noRecords: @json(__('milk.no_data')) },
    });
});
</script>
@endpush