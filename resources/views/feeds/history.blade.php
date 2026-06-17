@extends('layouts.app')
@section('title', 'Feed Stock History')

@section('content')

<x-section-header title="Feed Stock Movement History" icon="📒">
    <x-slot:actions>
        <a href="{{ route('feeds.index') }}" class="btn btn-outline btn-sm">← Back to Inventory</a>
    </x-slot:actions>
</x-section-header>

<x-form-card title="Filters" icon="🔍">
    <form method="GET" class="ds-form-grid ds-form-grid-3 align-items-end">
        <div class="form-group">
            <label class="form-label">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="form-group">
            <label class="form-label">To Date</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Feed</label>
            <select name="feed_id" class="form-control">
                <option value="">All Feeds</option>
                @foreach($feeds as $f)
                <option value="{{ $f->id }}" {{ request('feed_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Type</label>
            <select name="transaction_type" class="form-control">
                <option value="">IN + OUT</option>
                <option value="IN" {{ request('transaction_type') === 'IN' ? 'selected' : '' }}>IN</option>
                <option value="OUT" {{ request('transaction_type') === 'OUT' ? 'selected' : '' }}>OUT</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>
</x-form-card>

<x-form-card title="Stock Movements" icon="📋" :flush="true">
    <x-responsive-table>
        <table class="ds-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Feed</th>
                    <th>Type</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Amount</th>
                    <th>Supplier / Note</th>
                    <th>Daily Report</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td>{{ $txn->transaction_date->format('d-m-Y') }}</td>
                    <td><strong>{{ $txn->feed?->name }}</strong> <small class="text-muted">{{ $txn->feed?->unit }}</small></td>
                    <td>
                        <span class="badge {{ $txn->direction === 'in' ? 'badge-green' : 'badge-red' }}">{{ $txn->ledger_type }}</span>
                        @if($txn->feed_time)<small class="text-muted">({{ $txn->feed_time }})</small>@endif
                    </td>
                    <td class="text-end">{{ number_format($txn->quantity, 2) }}</td>
                    <td class="text-end">{{ $txn->rate ? number_format($txn->rate, 2) : '—' }}</td>
                    <td class="text-end">{{ $txn->total_amount ? '₹'.number_format($txn->total_amount, 2) : '—' }}</td>
                    <td>{{ $txn->supplier ?? $txn->remarks ?? '—' }}</td>
                    <td>
                        @if($txn->daily_report_id)
                        <a href="{{ route('daily-reports.show', $txn->daily_report_id) }}">#{{ $txn->daily_report_id }}</a>
                        @else — @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center" style="padding:2rem;color:#94a3b8;">No transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
    <div style="padding:12px 16px;">{{ $transactions->links() }}</div>
</x-form-card>
@endsection
