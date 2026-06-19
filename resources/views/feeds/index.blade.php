@extends('layouts.app')
@section('title', 'ફીડ ઇન્વેન્ટરી')

@section('content')

<x-section-header title="Feed Inventory" icon="🌾" subtitle="Stock IN via purchase · Stock OUT via Daily Report consumption">
    <x-slot:actions>
        <a href="{{ route('feeds.history') }}" class="btn btn-outline btn-sm">📒 Stock History</a>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('stockInModal').classList.add('open')">➕ Add Stock IN</button>
        <a href="{{ route('feeds.create') }}" class="btn btn-success btn-sm">➕ New Feed Type</a>
    </x-slot:actions>
</x-section-header>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
<div class="alert alert-info">{{ session('info') }}</div>
@endif

<div class="ds-stats-grid ds-stats-grid-4">
    <x-stat-card variant="plain" label="Total Feed Types" :value="$stats['feed_types']" />
    <x-stat-card variant="plain" label="Total Stock Value" :value="'₹' . number_format($stats['stock_value'], 0)" />
    <x-stat-card variant="plain" label="Today's Consumption" :value="number_format($stats['today_consumption'], 1)" valueClass="text-danger" />
    <x-stat-card variant="plain" label="Current Inventory" :value="number_format($stats['current_inventory'], 1)" valueClass="text-success" />
</div>

<x-form-card title="Feed Stock List" icon="📋" :flush="true">
    <x-erp-listing :paginator="$feeds" :per-page="$perPage" :search="true" search-placeholder="ફીડ નામ શોધો..." id="feeds">
    <x-responsive-table>
        <table class="ds-table">
            <thead>
                <tr>
                    <th>{{ __('common.sr_no') }}</th>
                    <th>Feed Name</th>
                    <th class="text-end">Available Qty</th>
                    <th class="text-center">Unit</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Alert</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feeds as $feed)
                <tr>
                    <td data-label="{{ __('common.sr_no') }}">{{ $feeds->firstItem() + $loop->index }}</td>
                    <td data-label="Feed">
                        <strong>{{ $feed->name }}</strong>
                        @if($feed->description)
                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($feed->description, 50) }}</small>
                        @endif
                    </td>
                    <td class="text-end" data-label="Available">
                        @php $avail = $feed->available_quantity; @endphp
                        <strong class="text-primary">{{ fmod($avail, 1) == 0 ? number_format($avail, 0) : number_format($avail, 2) }}</strong>
                    </td>
                    <td class="text-center" data-label="Unit"><span class="badge badge-blue">{{ $feed->unit }}</span></td>
                    <td class="text-center" data-label="Status">
                        @if($feed->status)
                        <span class="badge badge-green">Active</span>
                        @else
                        <span class="badge badge-red">Inactive</span>
                        @endif
                    </td>
                    <td class="text-center" data-label="Alert">
                        @if($feed->isLowStock())
                        <span class="low-stock-badge">🔴 Low Stock</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td data-label="" class="mobile-card-actions erp-listing__actions" style="white-space:nowrap;">
                        <div class="mobile-card-actions__group">
                        <a href="{{ route('feeds.show', $feed) }}" class="btn btn-outline btn-sm" title="Ledger">👁</a>
                        <a href="{{ route('feeds.edit', $feed) }}" class="btn btn-ghost btn-sm" title="Edit">✏️</a>
                        <button type="button" class="btn btn-primary btn-sm" title="Add Stock" onclick="openStockInFor({{ $feed->id }})">➕</button>
                        <form method="POST" action="{{ route('feeds.destroy', $feed) }}" class="d-inline" onsubmit="return confirm('Delete this feed type?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">🗑</button>
                        </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center" style="padding:2rem;color:#94a3b8;">No feeds yet. Create your first feed type.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
    </x-erp-listing>
</x-form-card>

<div id="stockInModal" class="ds-modal" role="dialog">
    <div class="ds-modal__backdrop" onclick="document.getElementById('stockInModal').classList.remove('open')"></div>
    <div class="ds-modal__box">
        <h5 class="mb-3">➕ Add Stock (IN)</h5>
        <form method="POST" id="stockInForm" action="">
            @csrf
            <div class="form-group">
                <label class="form-label">Date *</label>
                <input type="date" name="transaction_date" class="form-control" value="{{ today()->toDateString() }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Feed *</label>
                <select id="stockInFeedSelect" class="form-control" required onchange="updateStockInAction()">
                    <option value="">Select feed</option>
                    @foreach($allFeeds as $f)
                    <option value="{{ $f->id }}">{{ $f->name }} ({{ $f->unit }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity *</label>
                <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" required>
            </div>
            <div class="ds-form-grid ds-form-grid-2">
                <div class="form-group">
                    <label class="form-label">Rate (₹ per unit)</label>
                    <input type="number" step="0.01" min="0" name="rate" class="form-control" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Supplier</label>
                    <input type="text" name="supplier" class="form-control" placeholder="Supplier name">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Remarks</label>
                <input type="text" name="remarks" class="form-control" placeholder="Purchase note">
            </div>
            <div class="form-group">
                <label class="form-label">Expense amount (₹) — optional</label>
                <input type="number" step="0.01" min="0" name="purchase_amount" class="form-control">
            </div>
            <div class="d-flex gap-2" style="justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('stockInModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Stock IN</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function updateStockInAction() {
    const id = document.getElementById('stockInFeedSelect').value;
    const form = document.getElementById('stockInForm');
    if (id) form.action = '{{ url('feeds') }}/' + id + '/stock-in';
}
function openStockInFor(feedId) {
    document.getElementById('stockInFeedSelect').value = feedId;
    updateStockInAction();
    document.getElementById('stockInModal').classList.add('open');
}
</script>
@endpush
@endsection
