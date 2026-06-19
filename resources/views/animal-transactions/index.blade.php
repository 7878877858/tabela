@extends('layouts.app')
@section('title', __('farm.animal_transactions'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('farm.animal_transactions')" icon="🐃">
    <x-slot:actions>
        <a href="{{ route('buffalo.create') }}" class="btn btn-primary btn-sm">➕ {{ __('common.add_buffalo') }}</a>
        <a href="{{ route('reports.animal-purchases') }}" class="btn btn-ghost btn-sm">📊 {{ __('farm.report_animal_purchase') }}</a>
        <a href="{{ route('reports.animal-sales') }}" class="btn btn-ghost btn-sm">📊 {{ __('income.animal_sale_report') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<form method="GET" class="erp-panel" style="margin-bottom:16px; display:flex; gap:12px;">
    <select name="type" class="form-control form-control-sm" onchange="this.form.submit()">
        <option value="">{{ __('farm.transaction_history') }} — બધા</option>
        <option value="purchase" @selected(($type ?? '') === 'purchase')>{{ __('farm.purchase') }}</option>
        <option value="sale" @selected(($type ?? '') === 'sale')>{{ __('farm.sale') }}</option>
    </select>
</form>

<x-form-card :title="__('farm.transaction_history')" icon="📋" :flush="true">
    <x-erp-listing :paginator="$transactions" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('farm.date') }}</th>
                        <th>{{ __('farm.transaction_type') }}</th>
                        <th>{{ __('farm.animal') }}</th>
                        <th>{{ __('farm.counterparty') }}</th>
                        <th class="text-end">{{ __('farm.amount') }}</th>
                        <th>{{ __('farm.remarks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                    <tr>
                        <td>{{ $txn->transaction_date->format('d-m-Y') }}</td>
                        <td>{{ $txn->transaction_type === 'purchase' ? __('farm.purchase') : __('farm.sale') }}</td>
                        <td>{{ $txn->buffalo?->display_label ?? '—' }}</td>
                        <td>{{ $txn->counterparty_name ?? '—' }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($txn->amount, 2) }}</td>
                        <td>{{ $txn->remarks ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:24px;">{{ __('farm.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection
