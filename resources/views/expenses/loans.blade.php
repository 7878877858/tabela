@extends('layouts.app')
@section('title', __('farm.loans'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('farm.loans')" icon="🏦">
    <x-slot:actions>
        <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm">← {{ __('farm.expenses_hub') }}</a>
        <a href="{{ route('reports.loans') }}" class="btn btn-outline btn-sm">📊 {{ __('farm.report_loan') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<x-form-card :title="__('farm.add') . ' — ' . __('farm.loans')" icon="➕">
    <form method="POST" action="{{ route('expenses.loans.store') }}" class="grid-3">
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('farm.loan_name') }} *</label>
            <input type="text" name="loan_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.bank_name') }}</label>
            <input type="text" name="bank_name" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.loan_amount') }} *</label>
            <input type="number" step="0.01" min="0" name="loan_amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.emi_amount') }}</label>
            <input type="number" step="0.01" min="0" name="emi_amount" class="form-control" value="0">
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.start_date') }} *</label>
            <input type="date" name="start_date" class="form-control" value="{{ today()->toDateString() }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.end_date') }}</label>
            <input type="date" name="end_date" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.outstanding_balance') }}</label>
            <input type="number" step="0.01" min="0" name="outstanding_balance" class="form-control" value="0">
        </div>
        <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label">{{ __('farm.remarks') }}</label>
            <input type="text" name="remarks" class="form-control">
        </div>
        <div><button type="submit" class="btn btn-primary">{{ __('farm.add') }}</button></div>
    </form>
</x-form-card>

<x-form-card :title="__('farm.loans')" icon="📋" :flush="true" style="margin-top:16px;">
    <x-erp-listing :paginator="$loans" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('farm.loan_name') }}</th>
                        <th>{{ __('farm.bank_name') }}</th>
                        <th class="text-end">{{ __('farm.loan_amount') }}</th>
                        <th class="text-end">{{ __('farm.emi_amount') }}</th>
                        <th class="text-end">{{ __('farm.outstanding_balance') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                    <tr>
                        <td>{{ $loan->loan_name }}</td>
                        <td>{{ $loan->bank_name ?? '—' }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($loan->loan_amount, 2) }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($loan->emi_amount, 2) }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($loan->outstanding_balance, 2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('expenses.loans.destroy', $loan) }}" onsubmit="return confirm('ડિલીટ કરવું?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-danger">{{ __('farm.delete') }}</button>
                            </form>
                        </td>
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
