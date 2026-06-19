@extends('layouts.app')
@section('title', __('animal_investment.title'))

@section('content')
<link rel="stylesheet" href="{{ asset('static/css/dashboard.css') }}">

<x-section-header :title="__('animal_investment.title')" icon="🏦">
    <x-slot:actions>
        <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">← {{ __('dashboard.title') }}</a>
    </x-slot:actions>
</x-section-header>

<div class="erp-panel" style="margin-bottom: var(--ds-space-4, 16px);">
    <form method="GET" action="{{ route('reports.animal-investment') }}" class="d-flex gap-2 flex-wrap align-items-end">
        <div class="form-group mb-0">
            <label class="form-label">{{ __('animal_investment.date_from') }}</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('animal_investment.date_to') }}</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">{{ __('animal_investment.animal_type') }}</label>
            <select name="animal_type" class="form-control form-control-sm">
                <option value="">{{ __('animal_investment.all_types') }}</option>
                @foreach($animalTypes as $key => $label)
                <option value="{{ $key }}" @selected($animalType === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-0" style="min-width: 200px;">
            <label class="form-label">{{ __('animal_investment.search') }}</label>
            <input type="search" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="{{ __('animal_investment.search_placeholder') }}">
        </div>
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <button type="submit" class="btn btn-primary btn-sm">{{ __('animal_investment.apply_filters') }}</button>
    </form>
    <p class="text-muted mt-2 mb-0" style="font-size: 13px;">ℹ️ {{ __('animal_investment.investment_note') }}</p>
</div>

<div class="erp-kpi-grid erp-kpi-grid--5" style="margin-bottom: var(--ds-space-4, 16px);">
    <x-dashboard-kpi icon="🐃" accent="blue" :value="$currency . number_format($totals['buffalo'], 0)" :label="__('animal_investment.total_buffalo')" />
    <x-dashboard-kpi icon="🐄" accent="green" :value="$currency . number_format($totals['cow'], 0)" :label="__('animal_investment.total_cow')" />
    <x-dashboard-kpi icon="🐃" accent="orange" :value="$currency . number_format($totals['buffalo_calf'], 0)" :label="__('animal_investment.total_buffalo_calf')" />
    <x-dashboard-kpi icon="🐄" accent="purple" :value="$currency . number_format($totals['cow_calf'], 0)" :label="__('animal_investment.total_cow_calf')" />
    <x-dashboard-kpi icon="💰" accent="gold" :value="$currency . number_format($grandTotal, 0)" :label="__('animal_investment.grand_total')" />
</div>

<x-form-card :title="__('animal_investment.title')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$records" :per-page="$perPage" :search="false" id="animal-investment">
    <x-responsive-table>
        <table class="ds-table">
            <thead>
                <tr>
                    <th>{{ __('common.sr_no') }}</th>
                    <th>{{ __('animal_investment.col_date') }}</th>
                    <th>{{ __('animal_investment.col_tag') }}</th>
                    <th>{{ __('animal_investment.col_type') }}</th>
                    <th>{{ __('animal_investment.col_name') }}</th>
                    <th>{{ __('animal_investment.col_price') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $buffalo)
                <tr>
                    <td data-label="{{ __('common.sr_no') }}">{{ $records->firstItem() + $loop->index }}</td>
                    <td data-label="{{ __('animal_investment.col_date') }}">{{ $buffalo->purchase_date?->format('d-m-Y') ?? '—' }}</td>
                    <td data-label="{{ __('animal_investment.col_tag') }}"><strong>{{ $buffalo->tag_number }}</strong></td>
                    <td data-label="{{ __('animal_investment.col_type') }}">{{ $buffalo->animal_type_label }}</td>
                    <td data-label="{{ __('animal_investment.col_name') }}">
                        <a href="{{ route('buffalo.show', $buffalo) }}">{{ $buffalo->name ?? '—' }}</a>
                    </td>
                    <td data-label="{{ __('animal_investment.col_price') }}"><strong>{{ $currency }}{{ number_format((float) $buffalo->purchase_price, 0) }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 24px;">{{ __('animal_investment.no_records') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection
