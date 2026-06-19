@extends('layouts.app')
@section('title', __('milk_flow.milk_customers'))

@section('content')
@php
    $milkCustomersCssVer = @filemtime(public_path('static/css/milk-customers.css')) ?: '1';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('static/css/milk-customers.css') }}?v={{ $milkCustomersCssVer }}">
@endpush

<div class="milk-customers-page">

<x-section-header :title="__('milk_flow.milk_customers')" icon="👥">
    <x-slot:actions>
        <a href="{{ route('milk-distribution.index') }}" class="btn btn-outline btn-sm">← {{ __('milk_flow.milk_distribution') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<x-form-card :title="__('milk_flow.add_customer')" icon="➕">
    <form method="POST" action="{{ route('milk-customers.store') }}">
        @csrf
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('milk_flow.name') }} *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('milk_flow.mobile') }}</label>
                <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('milk_flow.address') }}</label>
            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
        </div>
        <button type="submit" class="btn btn-primary">{{ __('milk_flow.add_customer') }}</button>
    </form>
</x-form-card>

<x-form-card :title="__('milk_flow.milk_customers')" icon="📋" :flush="true" style="margin-top:16px;">
    <x-erp-listing :paginator="$customers" :per-page="$perPage" :search="true" search-placeholder="નામ / મોબાઇલ શોધો..." id="milk-customers">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('common.sr_no') }}</th>
                        <th>{{ __('milk_flow.name') }}</th>
                        <th>{{ __('milk_flow.mobile') }}</th>
                        <th>{{ __('milk_flow.address') }}</th>
                        <th>{{ __('milk_flow.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td data-label="{{ __('common.sr_no') }}">{{ $customers->firstItem() + $loop->index }}</td>
                        <td data-label="{{ __('milk_flow.name') }}"><strong>{{ $customer->name }}</strong></td>
                        <td data-label="{{ __('milk_flow.mobile') }}">{{ $customer->mobile ?? '—' }}</td>
                        <td data-label="{{ __('milk_flow.address') }}">{{ $customer->address ?? '—' }}</td>
                        <td data-label="{{ __('milk_flow.status') }}">
                            <span class="badge {{ $customer->status === 'active' ? 'badge-green' : 'badge-red' }}">
                                {{ $customer->status === 'active' ? __('milk_flow.active') : __('milk_flow.inactive') }}
                            </span>
                        </td>
                        <td class="action-column" data-label="Action">
                            <div class="table-actions">
                                <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEdit({{ $customer->id }})">✏️</button>
                                <form method="POST" action="{{ route('milk-customers.destroy', $customer) }}" onsubmit="return confirm('ડિલીટ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr id="edit-{{ $customer->id }}" hidden>
                        <td colspan="6">
                            <form method="POST" action="{{ route('milk-customers.update', $customer) }}" class="erp-panel" style="padding:12px;">
                                @csrf @method('PATCH')
                                <div class="grid-3">
                                    <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                                    <input type="text" name="mobile" class="form-control" value="{{ $customer->mobile }}">
                                    <select name="status" class="form-control">
                                        <option value="active" @selected($customer->status === 'active')>{{ __('milk_flow.active') }}</option>
                                        <option value="inactive" @selected($customer->status === 'inactive')>{{ __('milk_flow.inactive') }}</option>
                                    </select>
                                </div>
                                <input type="text" name="address" class="form-control mt-2" value="{{ $customer->address }}">
                                <button type="submit" class="btn btn-primary btn-sm mt-2">સાચવો</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:24px;">{{ __('common.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>

<script>
function toggleEdit(id) {
    const row = document.getElementById('edit-' + id);
    if (row) row.hidden = !row.hidden;
}
</script>
</div>
@endsection
