@extends('layouts.app')
@section('title', 'બચ્ચા જન્મ ઇતિહાસ')

@section('content')

<x-section-header title="બચ્ચા જન્મ ઇતિહાસ" icon="🐄">
    <x-slot:actions>
        <button type="button" class="btn btn-outline btn-sm" onclick="window.print()">🖨️ પ્રિન્ટ</button>
    </x-slot:actions>
</x-section-header>

<div class="erp-panel no-print" style="margin-bottom: 16px;">
    <form method="GET" action="{{ route('reports.birth-history') }}" class="d-flex gap-2 flex-wrap align-items-end">
        <div class="form-group mb-0">
            <label class="form-label">તારીખથી</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">તારીખ સુધી</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>
        <div class="form-group mb-0" style="min-width: 220px;">
            <label class="form-label">શોધો</label>
            <input type="search" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="ટેગ / નામ / વાછરડો ટેગ">
        </div>
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <button type="submit" class="btn btn-primary btn-sm">લાગુ કરો</button>
    </form>
</div>

<x-form-card title="બચ્ચા જન્મ ઇતિહાસ" icon="📋" :flush="true">
    <x-erp-listing :paginator="$births" :per-page="$perPage" :search="false" id="birth-history">
        <x-responsive-table>
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>{{ __('common.sr_no') }}</th>
                        <th>પશુ</th>
                        <th>જન્મ તારીખ</th>
                        <th>વાછરડાનો ટેગ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($births as $birth)
                    <tr>
                        <td data-label="{{ __('common.sr_no') }}">{{ $births->firstItem() + $loop->index }}</td>
                        <td data-label="પશુ">{{ $birth->mother_display_label }}</td>
                        <td data-label="જન્મ તારીખ">{{ $birth->birth_date?->format('d-m-Y') ?? '—' }}</td>
                        <td data-label="વાછરડાનો ટેગ">{{ $birth->calf_birth_tag }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted" style="padding: 24px;">ડેટા નથી</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
    </x-erp-listing>
</x-form-card>

<style>
@media print {
    .sidebar, .topbar, .no-print { display: none !important; }
}
</style>

@endsection
