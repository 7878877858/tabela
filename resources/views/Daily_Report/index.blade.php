@extends('layouts.app')

@section('title', 'દૈનિક સ્ટાફ કાર્ય અહેવાલ')

@section('content')

<div class="daily-report-list-page">

<x-section-header title="દૈનિક સ્ટાફ કાર્ય અહેવાલ" icon="📋" subtitle="Daily Dairy Farm Report Management">
    <x-slot:actions>
        <a href="{{ route('daily-reports.create') }}" class="btn btn-success">➕ નવો અહેવાલ</a>
    </x-slot:actions>
</x-section-header>

<div class="ds-stats-grid ds-stats-grid-4">
    <x-stat-card color="blue" icon="📋" label="કુલ રિપોર્ટ" :value="$reports->total()" />
    <x-stat-card color="green" icon="📅" label="આજની તારીખ" :value="now()->format('d')" />
    <x-stat-card color="orange" icon="🐄" label="કુલ પશુ" :value="$totalAnimals" />
    <x-stat-card color="red" icon="👷" label="કુલ કર્મચારી" :value="$totalStaff" />
</div>

<x-form-card title="રિપોર્ટ યાદી" icon="📑" :flush="true">
    <x-erp-listing :paginator="$reports" :per-page="$perPage" :search="true" search-placeholder="અહેવાલ / બનાવનાર શોધો..." id="daily-reports">
    <x-responsive-table>
        <table class="ds-table">
            <thead>
                <tr>
                    <th>{{ __('common.sr_no') }}</th>
                    <th>તારીખ</th>
                    <th>શિફ્ટ</th>
                    <th>અહેવાલ નંબર</th>
                    <th>બનાવનાર</th>
                    <th class="action-column">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr class="dr-list-row">
                    <td data-label="{{ __('common.sr_no') }}">{{ $reports->firstItem() + $loop->index }}</td>
                    <td data-label="તારીખ">{{ $report->report_date->format('d-m-Y') }}</td>
                    <td data-label="શિફ્ટ">{{ $report->shift ?? '—' }}</td>
                    <td data-label="અહેવાલ">{{ $report->report_number ?? ('DR-' . $report->id) }}</td>
                    <td data-label="બનાવનાર">{{ $report->reporter ?? '—' }}</td>
                    <td data-label="" class="mobile-card-actions erp-listing__actions dr-row-actions action-column">
                        <div class="mobile-card-actions__group dr-action-group action-buttons">
                            <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-outline btn-sm dr-action-btn" title="View">👁</a>
                            <a href="{{ route('daily-reports.edit', $report) }}" class="btn btn-ghost btn-sm dr-action-btn" title="Edit">✏️</a>
                            <form method="POST" action="{{ route('daily-reports.destroy', $report) }}" class="dr-action-form action-form" onsubmit="return confirm('શું તમે ખરેખર ડિલીટ કરવા માંગો છો?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm dr-action-btn" title="Delete">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center" style="padding:2rem;color:#94a3b8;">કોઈ અહેવાલ નથી</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
    </x-erp-listing>
</x-form-card>

</div>

@if(session('clear_daily_report_draft'))
@push('scripts')
<script>
(function () {
    const DB_NAME = 'tabela_daily_report';
    const STORE = 'drafts';
    const LS_KEY = 'daily_report_draft_v2';
    try {
        localStorage.removeItem(LS_KEY);
        localStorage.removeItem('daily_report_draft');
    } catch (e) {}
    if ('indexedDB' in window) {
        const req = indexedDB.open(DB_NAME, 1);
        req.onupgradeneeded = function () {
            const db = req.result;
            if (!db.objectStoreNames.contains(STORE)) db.createObjectStore(STORE);
        };
        req.onsuccess = function () {
            const db = req.result;
            if (!db.objectStoreNames.contains(STORE)) return;
            const tx = db.transaction(STORE, 'readwrite');
            tx.objectStore(STORE).clear();
        };
    }
})();
</script>
@endpush
@endif

@endsection
