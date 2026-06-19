@extends('layouts.app')

@section('title', 'Feed Ledger - '.$feed->name)

@section('content')

<x-section-header :title="$feed->name" icon="🌾" subtitle="Feed detail & stock ledger">
    <x-slot:actions>
        <button type="button" class="btn btn-primary btn-sm" data-toggle-stock-in>➕ Add Stock</button>
        <a href="{{ route('feeds.edit', $feed) }}" class="btn btn-outline btn-sm">✏️ Edit</a>
        <a href="{{ route('feeds.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@php
    $opening = $feed->opening_stock;
    $available = $feed->available_quantity;
    $totalIn = (float) ($feed->total_in ?? 0);
    $totalOut = (float) ($feed->total_out ?? 0);
    $stockValue = $feed->estimatedStockValue();
@endphp

<div class="ds-stats-grid ds-stats-grid-4">
    <x-stat-card variant="plain" label="Current Stock" :value="number_format($available, fmod($available, 1) == 0 ? 0 : 2) . ' ' . $feed->unit" />
    <x-stat-card variant="plain" label="Opening Stock" :value="number_format($opening, fmod($opening, 1) == 0 ? 0 : 2) . ' ' . $feed->unit" />
    <x-stat-card variant="plain" label="Total IN / OUT" :value="number_format($totalIn, 0) . ' / ' . number_format($totalOut, 0)" />
    <x-stat-card variant="plain" label="Stock Value (est.)" :value="'₹' . number_format($stockValue, 0)" />
</div>

<div class="grid-2 mb-4">
    <x-form-card title="Feed Info" icon="ℹ️">
        <table class="ds-table">
            <tbody>
                <tr><td class="text-muted">Unit</td><td>{{ $feed->unit }}</td></tr>
                <tr><td class="text-muted">Low stock threshold</td><td>{{ number_format($feed->min_stock, 2) }} {{ $feed->unit }}</td></tr>
                <tr><td class="text-muted">Status</td><td>{{ $feed->status ? 'Active' : 'Inactive' }}</td></tr>
                <tr><td class="text-muted">Description</td><td>{{ $feed->description ?: '—' }}</td></tr>
            </tbody>
        </table>
        @if($feed->isLowStock())<p class="mb-0 mt-2"><span class="low-stock-badge">🔴 Low Stock</span></p>@endif
    </x-form-card>
    <x-form-card title="Add Stock (IN)" icon="➕" id="stockInPanel" style="display:none;">
            <form method="POST" action="{{ route('feeds.stock-in', $feed) }}">
                @csrf
                <div class="ds-form-grid ds-form-grid-2">
                    <div class="form-group"><label class="form-label">Date</label><input type="date" name="transaction_date" class="form-control" value="{{ today()->toDateString() }}" required></div>
                    <div class="form-group"><label class="form-label">Quantity</label><input type="number" step="0.01" min="0.01" name="quantity" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Rate</label><input type="number" step="0.01" min="0" name="rate" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Supplier</label><input type="text" name="supplier" class="form-control"></div>
                    <div class="form-group" style="grid-column:1/-1;"><label class="form-label">Remarks</label><input type="text" name="remarks" class="form-control"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Save IN</button>
            </form>
    </x-form-card>
</div>

<x-form-card title="Feed Ledger" icon="📒" :flush="true">
    <x-responsive-table :mobileCards="false">
        <table class="ds-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Balance</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @php $running = $opening; @endphp
                @if($opening > 0)
                <tr>
                    <td>—</td>
                    <td><span class="badge bg-secondary">OPEN</span></td>
                    <td class="text-end">{{ number_format($opening, fmod($opening, 1) == 0 ? 0 : 2) }}</td>
                    <td class="text-end"><strong>{{ number_format($running, fmod($running, 1) == 0 ? 0 : 2) }}</strong></td>
                    <td>Opening stock (feeds.volume)</td>
                </tr>
                @endif
                @forelse($ledger as $txn)
                @php
                    $running += $txn->direction === 'in' ? (float)$txn->quantity : -(float)$txn->quantity;
                @endphp
                <tr>
                    <td>{{ $txn->transaction_date->format('d-m-Y') }}</td>
                    <td>
                        <span class="badge {{ $txn->direction === 'in' ? 'badge-green' : 'badge-red' }}">{{ $txn->ledger_type }}</span>
                        @if($txn->feed_time)<small>({{ $txn->feed_time }})</small>@endif
                    </td>
                    <td class="text-end">{{ number_format($txn->quantity, 2) }}</td>
                    <td class="text-end"><strong>{{ number_format($running, 2) }}</strong></td>
                    <td>{{ $txn->remarks ?? $txn->supplier ?? '—' }}</td>
                </tr>
                @empty
                @if($opening <= 0)
                <tr><td colspan="5" class="text-center text-muted py-3">No ledger entries.</td></tr>
                @endif
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
</x-form-card>

<x-form-card title="All Transactions" icon="📋" :flush="true">
    <x-erp-listing :paginator="$transactions" :per-page="$perPage" :search="true" search-placeholder="સપ્લાયર / રિમાર્ક શોધો..." id="feed-show">
    <x-responsive-table>
        <table class="ds-table">
            <thead>
                <tr>
                    <th>{{ __('common.sr_no') }}</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>IN/OUT</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Supplier</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td>{{ $transactions->firstItem() + $loop->index }}</td>
                    <td>{{ $txn->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $txn->type_label }}</td>
                    <td><span class="badge {{ $txn->direction === 'in' ? 'badge-green' : 'badge-red' }}">{{ $txn->ledger_type }}</span></td>
                    <td>{{ number_format($txn->quantity, 2) }} {{ $feed->unit }}</td>
                    <td>{{ $txn->rate ? number_format($txn->rate, 2) : '—' }}</td>
                    <td>{{ $txn->supplier ?? '—' }}</td>
                    <td>{{ $txn->remarks ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-3">No transactions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
    </x-erp-listing>
</x-form-card>

@push('scripts')
<script>
document.querySelector('[data-toggle-stock-in]')?.addEventListener('click', function() {
    const p = document.getElementById('stockInPanel');
    if (p) p.style.display = p.style.display === 'none' ? 'block' : 'none';
});
</script>
@endpush
@endsection
