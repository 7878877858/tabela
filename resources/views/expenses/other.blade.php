@extends('layouts.app')
@section('title', __('farm.other_expenses'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('farm.other_expenses')" icon="📋">
    <x-slot:actions>
        <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm">← {{ __('farm.expenses_hub') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<x-form-card :title="__('farm.add') . ' — ' . __('farm.other_expenses')" icon="➕">
    <form method="POST" action="{{ route('expenses.other.store') }}" class="grid-3">
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('farm.expense_category') }} *</label>
            <input type="text" name="category" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.amount') }} *</label>
            <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.date') }} *</label>
            <input type="date" name="expense_date" class="form-control" value="{{ today()->toDateString() }}" required>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label">{{ __('farm.remarks') }}</label>
            <input type="text" name="remarks" class="form-control">
        </div>
        <div><button type="submit" class="btn btn-primary">{{ __('farm.add') }}</button></div>
    </form>
</x-form-card>

<x-form-card :title="__('farm.other_expenses')" icon="📋" :flush="true" style="margin-top:16px;">
    <x-erp-listing :paginator="$expenses" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('farm.date') }}</th>
                        <th>{{ __('farm.expense_category') }}</th>
                        <th class="text-end">{{ __('farm.amount') }}</th>
                        <th>{{ __('farm.remarks') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr>
                        <td>{{ $expense->expense_date->format('d-m-Y') }}</td>
                        <td>{{ $expense->category }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->remarks ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('expenses.other.destroy', $expense) }}" onsubmit="return confirm('ડિલીટ કરવું?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-danger">{{ __('farm.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted" style="padding:24px;">{{ __('farm.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection
