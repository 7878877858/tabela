@extends('layouts.app')
@section('title', __('buffalo.all_buffaloes'))

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/daily-report.css') }}">

<x-section-header :title="__('buffalo.all_buffaloes')" icon="🐃">
    <x-slot:actions>
        <a href="{{ route('buffalo.create') }}" class="btn btn-primary">➕ {{ __('buffalo.new_buffalo') }}</a>
    </x-slot:actions>
</x-section-header>

<div class="animal-list-page daily-report-page">
    <x-form-card :title="__('buffalo.all_buffaloes')" icon="🐃" :flush="true">
        <div class="dr-section-table-area">
            <div class="dr-grid-toolbar animal-list-toolbar">
                <div id="buffaloAnimalTabs" class="dr-animal-tabs" role="tablist" aria-label="Animal type filter"></div>
                <div class="dr-grid-toolbar__search">
                    <input type="search" id="buffaloAnimalSearch" class="form-control form-control-sm" placeholder="ટેગ / નામ શોધો..." autocomplete="off">
                </div>
                <span class="dr-grid-toolbar__meta text-muted" id="buffaloListCounts">{{ array_sum($animalTypeCounts ?? []) }} total</span>
            </div>

            <div class="table-responsive animal-list-table-wrap">
                <table class="ds-table animal-list-table" id="buffaloListTable">
                    <thead>
                        <tr>
                            <th>{{ __('buffalo.tag_number') }}</th>
                            <th>પ્રકાર</th>
                            <th>{{ __('buffalo.name') }}</th>
                            <th>{{ __('buffalo.status') }}</th>
                            <th>{{ __('buffalo.milk') }}</th>
                            <th>{{ __('buffalo.this_month') }}</th>
                            <th class="animal-list-actions-col"></th>
                        </tr>
                    </thead>
                    <tbody id="buffaloListBody"></tbody>
                </table>
            </div>

            <div id="buffaloListPagination" class="dr-grid-pagination"></div>
        </div>
    </x-form-card>
</div>

<script type="application/json" id="buffaloListJson">@json($animalsJson)</script>
@php
    $buffaloListConfig = [
        'csrf' => csrf_token(),
        'deleteConfirm' => __('buffalo.delete_confirm'),
        'createUrl' => route('buffalo.create'),
    ];
@endphp
<script type="application/json" id="buffaloListConfig">@json($buffaloListConfig)</script>

@push('scripts')
<script src="{{ asset('assets/js/buffalo-list.js') }}"></script>
@endpush

<style>
    .animal-list-page .dr-section-table-area {
        padding: 14px;
    }

    .animal-list-page .animal-list-toolbar {
        margin-bottom: 12px;
    }

    .animal-list-page .animal-list-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow-x: auto;
    }

    .animal-list-page .animal-list-table {
        margin-bottom: 0;
        width: 100%;
        min-width: 720px;
    }

    .animal-list-page .animal-list-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .animal-list-page .animal-list-actions,
    .animal-list-page .animal-list-actions-col {
        white-space: nowrap;
        text-align: right;
    }

    .animal-list-page .animal-list-actions .btn {
        margin-left: 2px;
    }

    @media (max-width: 767.98px) {
        .animal-list-page .dr-animal-tabs {
            width: 100%;
        }

        .animal-list-page .dr-animal-tab {
            flex: 1 1 calc(50% - 4px);
            justify-content: center;
        }

        .animal-list-page .dr-grid-toolbar__search {
            max-width: none;
            width: 100%;
        }
    }
</style>
@endsection
