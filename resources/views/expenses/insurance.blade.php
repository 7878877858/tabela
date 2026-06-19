@extends('layouts.app')
@section('title', __('farm.insurance'))

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp

<x-section-header :title="__('farm.insurance')" icon="🛡️">
    <x-slot:actions>
        <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm">← {{ __('farm.expenses_hub') }}</a>
        <a href="{{ route('reports.insurance') }}" class="btn btn-outline btn-sm">📊 {{ __('farm.report_insurance') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<x-form-card :title="__('farm.add') . ' — ' . __('farm.insurance')" icon="➕">
    <form method="POST" action="{{ route('expenses.insurance.store') }}" class="grid-3">
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('farm.insurance_type') }} *</label>
            <select name="insurance_type" class="form-control" required>
                <option value="animal">🐃 {{ __('farm.animal_insurance') }}</option>
                <option value="asset">🔧 {{ __('farm.asset_insurance') }}</option>
                <option value="vehicle">🚗 {{ __('farm.vehicle_insurance') }}</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.policy_number') }}</label>
            <input type="text" name="policy_number" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.premium_amount') }} *</label>
            <input type="number" step="0.01" min="0" name="premium_amount" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.start_date') }} *</label>
            <input type="date" name="start_date" class="form-control" value="{{ today()->toDateString() }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.expiry_date') }} *</label>
            <input type="date" name="expiry_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.status') }} *</label>
            <select name="status" class="form-control" required>
                <option value="active">{{ __('farm.active') }}</option>
                <option value="expired">{{ __('farm.expired') }}</option>
            </select>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label">{{ __('farm.remarks') }}</label>
            <input type="text" name="remarks" class="form-control">
        </div>
        <div><button type="submit" class="btn btn-primary">{{ __('farm.add') }}</button></div>
    </form>
</x-form-card>

<x-form-card :title="__('farm.insurance')" icon="📋" :flush="true" style="margin-top:16px;">
    <x-erp-listing :paginator="$policies" :per-page="$perPage" :search="false">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('farm.insurance_type') }}</th>
                        <th>{{ __('farm.policy_number') }}</th>
                        <th class="text-end">{{ __('farm.premium_amount') }}</th>
                        <th>{{ __('farm.start_date') }}</th>
                        <th>{{ __('farm.expiry_date') }}</th>
                        <th>{{ __('farm.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                    <tr>
                        <td>{{ __('farm.' . $policy->insurance_type . '_insurance') }}</td>
                        <td>{{ $policy->policy_number ?? '—' }}</td>
                        <td class="text-end">{{ $currency }}{{ number_format($policy->premium_amount, 2) }}</td>
                        <td>{{ $policy->start_date->format('d-m-Y') }}</td>
                        <td>{{ $policy->expiry_date->format('d-m-Y') }}</td>
                        <td>{{ $policy->status === 'active' ? __('farm.active') : __('farm.expired') }}</td>
                        <td>
                            <form method="POST" action="{{ route('expenses.insurance.destroy', $policy) }}" onsubmit="return confirm('ડિલીટ કરવું?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-danger">{{ __('farm.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted" style="padding:24px;">{{ __('farm.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>
@endsection
