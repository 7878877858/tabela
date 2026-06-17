@extends('layouts.app')
@section('title', __('milk.milk_entry'))

@section('content')

<x-section-header :title="__('milk.milk_entry')" icon="🥛">
    <x-slot:actions>
        <a href="{{ route('milk.history') }}" class="btn btn-outline btn-sm">📋 {{ __('milk.milk_history') }}</a>
        <a href="{{ route('daily-reports.create') }}" class="btn btn-primary btn-sm">📋 દૈનિક અહેવાલ</a>
    </x-slot:actions>
</x-section-header>

<div class="alert alert-success">
    💡 મુખ્ય દૂધ એન્ટ્રી માટે <a href="{{ route('daily-reports.create') }}"><strong>દૈનિક અહેવાલ</strong></a> વાપરો — એક જ જગ્યાએ દૂધ + ચારો + સ્ટાફ.
</div>

<x-form-card title="Date & Summary" icon="📅">
    <form method="GET" class="d-flex flex-wrap align-items-center gap-2">
        <div class="form-group" style="margin:0;min-width:180px;">
            <label class="form-label">{{ __('milk.date') }}</label>
            <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
        </div>
        <div style="font-size:0.875rem;color:var(--ds-text-muted);padding-top:1.5rem;">
            {{ __('milk.total') }}: <strong>{{ __('milk.morning') }} {{ number_format($totalMorning,1) }}L + {{ __('milk.evening') }} {{ number_format($totalEvening,1) }}L = {{ number_format($totalLiters,1) }}L</strong>
        </div>
    </form>
</x-form-card>

<div class="alert alert-warning">
    📋 <strong>Read-only view</strong> — synced from Daily Report. <a href="{{ route('daily-reports.create') }}">Enter data in Daily Report</a>
</div>

<x-form-card :title="__('milk.milk_entry')" icon="🥛" :flush="true">
    <x-responsive-table>
        <table class="ds-table">
            <thead>
                <tr>
                    <th>{{ __('milk.tag_name') }}</th>
                    <th>{{ __('milk.morning_liters') }} (L)</th>
                    <th>{{ __('milk.evening_liters') }} (L)</th>
                    <th>{{ __('milk.total_milk') }} (L)</th>
                    <th>{{ __('milk.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($buffaloes as $buffalo)
                @php $entry = $entries[$buffalo->id] ?? null; @endphp
                <tr>
                    <td data-label="{{ __('milk.tag_name') }}">
                        <strong>{{ $buffalo->tag_number }}</strong>
                        @if($buffalo->name) <span class="text-muted" style="font-size:12px;">{{ $buffalo->name }}</span> @endif
                    </td>
                    <td data-label="{{ __('milk.morning_liters') }}">{{ $entry ? number_format($entry->morning_liters, 2) : '—' }}</td>
                    <td data-label="{{ __('milk.evening_liters') }}">{{ $entry ? number_format($entry->evening_liters, 2) : '—' }}</td>
                    <td class="text-primary" style="font-weight:600;" data-label="{{ __('milk.total_milk') }}">{{ $entry ? number_format($entry->total_liters, 1) : '—' }}</td>
                    <td data-label="{{ __('milk.notes') }}">{{ $entry?->notes ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center" style="padding:2rem;color:#94a3b8;">{{ __('milk.no_active_buffalo') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
    @if($buffaloes->count() > 0)
    <p class="text-muted mb-0" style="padding:12px 16px;font-size:0.875rem;">
        {{ __('milk.total_buffaloes') }}: <strong>{{ $buffaloes->count() }}</strong>
    </p>
    @endif
</x-form-card>
@endsection
