@props([
    'paginator' => null,
    'perPage' => \App\Support\ListPagination::DEFAULT,
    'perPageOptions' => \App\Support\ListPagination::OPTIONS,
    'totalMeta' => null,
    'search' => false,
    'searchName' => 'search',
    'searchValue' => null,
    'searchPlaceholder' => null,
    'id' => 'listing',
])

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('static/css/erp-listing.css') }}">
    @endpush
@endonce

@php
    $searchValue = $searchValue ?? request($searchName, '');
    $searchPlaceholder = $searchPlaceholder ?? __('common.search_placeholder');
    $totalCount = $paginator?->total() ?? 0;
    $displayTotal = $totalMeta ?? __('common.total_records', ['total' => $totalCount]);
@endphp

<div {{ $attributes->merge(['class' => 'erp-listing']) }} id="erp-listing-{{ $id }}">
    <div class="erp-listing__toolbar{{ isset($filters) ? ' erp-listing__toolbar--has-filters' : '' }}">
        <div class="erp-listing__toolbar-start">
            @isset($filters)
                <div class="erp-listing__filter-tabs">{{ $filters }}</div>
            @endisset

            @if($search && !isset($filters))
                <form method="GET" class="erp-listing__search erp-listing__search-field">
                    @foreach(request()->except([$searchName, 'page']) as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $subKey => $subValue)
                                <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <span class="erp-listing__search-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </span>
                    <input type="search" name="{{ $searchName }}" class="erp-listing__search-input"
                           value="{{ $searchValue }}" placeholder="{{ $searchPlaceholder }}"
                           autocomplete="off">
                </form>
            @endif

            @isset($toolbar)
                <div class="erp-listing__search-slot">{{ $toolbar }}</div>
            @endisset
        </div>

        <div class="erp-listing__toolbar-end">
            <span class="erp-listing__total" id="erp-listing-total-{{ $id }}">{{ $displayTotal }}</span>
            @if($paginator)
                <form method="GET" class="erp-listing__per-page">
                    @foreach(request()->except(['per_page', 'page']) as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $subKey => $subValue)
                                <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label for="erp_per_page_{{ $id }}">{{ __('common.show') }}:</label>
                    <select id="erp_per_page_{{ $id }}" name="per_page" class="form-control form-control-sm" onchange="this.form.submit()">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>
            @else
                <div class="erp-listing__per-page">
                    <label for="erp_js_per_page_{{ $id }}">{{ __('common.show') }}:</label>
                    <select id="erp_js_per_page_{{ $id }}" class="form-control form-control-sm erp-listing__js-page-size" data-listing-id="{{ $id }}">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    <div class="erp-listing__table-wrap mobile-card-table ds-table-wrap table-responsive">
        {{ $slot }}
    </div>

    @if($paginator)
        <div class="erp-listing__footer">
            <div class="erp-listing__footer-info">
                @if($paginator->total() > 0)
                    {{ __('common.showing_records', [
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem(),
                        'total' => $paginator->total(),
                    ]) }}
                @else
                    {{ __('common.no_records') }}
                @endif
            </div>
            @if($paginator->hasPages())
                <div class="erp-listing__footer-nav">
                    {{ $paginator->onEachSide(2)->links('pagination.list') }}
                </div>
            @endif
        </div>
    @else
        <div id="erp-listing-footer-{{ $id }}" class="erp-listing__footer erp-listing__footer--js"></div>
    @endif
</div>
