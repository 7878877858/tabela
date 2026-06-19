@extends('layouts.app')
@section('title', __('farm.utility_bills'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('farm.utility_bills')" icon="💡">
    <x-slot:actions>
        <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm">← {{ __('farm.expenses_hub') }}</a>
        <a href="{{ route('reports.utility-bills') }}" class="btn btn-outline btn-sm">📊 {{ __('farm.report_utility') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<x-form-card :title="__('farm.add') . ' — ' . __('farm.utility_bills')" icon="➕">
    <form method="POST" action="{{ route('expenses.utility-bills.store') }}" class="grid-3">
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('farm.bill_type') }} *</label>
            <select name="bill_type" class="form-control" required>
                <option value="electricity">⚡ {{ __('farm.electricity') }}</option>
                <option value="water">💧 {{ __('farm.water') }}</option>
                <option value="internet">🌐 {{ __('farm.internet') }}</option>
                <option value="phone">📞 {{ __('farm.phone') }}</option>
                <option value="other">📋 {{ __('farm.other') }}</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.amount') }} *</label>
            <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.bill_date') }} *</label>
            <input type="date" name="bill_date" class="form-control" value="{{ today()->toDateString() }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.due_date') }}</label>
            <input type="date" name="due_date" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.paid_date') }}</label>
            <input type="date" name="paid_date" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.status') }} *</label>
            <select name="status" class="form-control" required>
                <option value="pending">{{ __('farm.pending') }}</option>
                <option value="paid">{{ __('farm.paid') }}</option>
            </select>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label">{{ __('farm.remarks') }}</label>
            <input type="text" name="remarks" class="form-control">
        </div>
        <div><button type="submit" class="btn btn-primary">{{ __('farm.add') }}</button></div>
    </form>
</x-form-card>

<x-form-card :title="__('farm.utility_bills')" icon="📋" :flush="true" style="margin-top:16px;">
    <x-erp-listing :paginator="$bills" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('farm.bill_date') }}</th>
                        <th>{{ __('farm.bill_type') }}</th>
                        <th class="text-end">{{ __('farm.amount') }}</th>
                        <th>{{ __('farm.status') }}</th>
                        <th>{{ __('farm.due_date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                    <tr>
                        <td>{{ $bill->bill_date->format('d-m-Y') }}</td>
                        <td>{{ __('farm.' . $bill->bill_type) }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($bill->amount, 2) }}</td>
                        <td>{{ $bill->status === 'paid' ? __('farm.paid') : __('farm.pending') }}</td>
                        <td>{{ $bill->due_date?->format('d-m-Y') ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('expenses.utility-bills.destroy', $bill) }}" onsubmit="return confirm('ડિલીટ કરવું?')">
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
