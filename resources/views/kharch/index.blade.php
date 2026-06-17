@extends('layouts.app')
@section('title', __('kharch.expense'))

@section('content')

<x-section-header :title="__('kharch.expense')" icon="💸" />

<div class="alert alert-warning">
    📋 <strong>Read-only report view</strong> — operational expenses sync from <a href="{{ route('daily-reports.create') }}">Daily Report</a>. Feed purchases via Feed stock-in remain separate.
</div>

{{-- Add form disabled: use Daily Report --}}
@if(false)
<div class="card" style="margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">{{ __('kharch.add_expense') }}</h3>
    <form method="POST" action="{{ route('kharch.store') }}">
        @csrf
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">{{ __('kharch.date') }} *</label>
                <input type="date" name="expense_date" class="form-control" value="{{ today()->toDateString() }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('kharch.category') }} *</label>
                <select name="category" class="form-control" required>
                    <option value="feed">🌾 {{ __('kharch.feed') }}</option>
                    <option value="medicine">💊 {{ __('kharch.medicine') }}</option>
                    <option value="labour">👷 {{ __('kharch.labour') }}</option>
                    <option value="equipment">🔧 {{ __('kharch.equipment') }}</option>
                    <option value="veterinary">🏥 {{ __('kharch.veterinary') }}</option>
                    <option value="other">📌 {{ __('kharch.other') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('kharch.amount') }} *</label>
                <input type="number" name="amount" step="0.01" min="0" class="form-control" placeholder="0.00" required>
            </div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('kharch.description') }} *</label>
                <input type="text" name="description" class="form-control" placeholder="{{ __('kharch.description_placeholder') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('kharch.buffalo') }} ({{ __('kharch.optional') }})</label>
                <select name="buffalo_id" class="form-control">
                    <option value="">— {{ __('kharch.all_buffaloes') }} —</option>
                    @foreach($buffaloes as $b)
                    <option value="{{ $b->id }}">{{ $b->tag_number }}{{ $b->name ? ' — '.$b->name : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">➕ {{ __('kharch.add') }}</button>
    </form>
</div>
@endif

{{-- Filter --}}
<form method="GET" style="display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap;">
    <select name="month" class="form-control" style="width:130px;" onchange="this.form.submit()">
        @foreach(range(1,12) as $m)
        <option value="{{ $m }}" {{ $m==$month ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->locale(app()->getLocale())->translatedFormat('F') }}
        </option>
        @endforeach
    </select>
    <select name="year" class="form-control" style="width:100px;" onchange="this.form.submit()">
        @foreach(range(now()->year, 2020) as $y)
        <option value="{{ $y }}" {{ $y==$year ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
    </select>
</form>

{{-- Summary --}}
<div class="summary-row">
   <span>
    📅 {{ \Carbon\Carbon::create()
        ->month($month)
        ->locale(app()->getLocale())
        ->translatedFormat('F') }}
    {{ $year }}
</span>
    <span>{{ __('kharch.total_expense') }}: <strong>₹{{ number_format($total,0) }}</strong></span>
    @foreach($byCategory as $cat)
    <span>{{ match($cat->category){'feed'=>'🌾 '.__('kharch.feed'),'medicine'=>'💊 '.__('kharch.medicine'),'labour'=>'👷 '.__('kharch.labour'),'equipment'=>'🔧 '.__('kharch.equipment'),'veterinary'=>'🏥 '.__('kharch.veterinary'),default=>'📌 '.__('kharch.other')} }}: <strong>₹{{ number_format($cat->total,0) }}</strong></span>
    @endforeach
</div>

{{-- Table --}}
<x-form-card :title="__('kharch.expense')" icon="📋" :flush="true">
    <x-responsive-table>
        <table class="ds-table">
            <thead>
                <tr><th>{{ __('kharch.date') }}</th><th>{{ __('kharch.category') }}</th><th>{{ __('kharch.description') }}</th><th>{{ __('kharch.buffalo') }}</th><th>{{ __('kharch.amount') }} (₹)</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($expenses as $e)
                <tr>
                    <td>{{ $e->expense_date->format('d/m/Y') }}</td>
                    <td><span class="badge badge-blue">{{ $e->category_label }}</span></td>
                    <td>{{ $e->description }}</td>
                    <td>{{ $e->buffalo?->tag_number ?? '—' }}</td>
                    <td><strong>₹{{ number_format($e->amount,0) }}</strong></td>
                    <td>
                        @if(!$e->daily_report_id)
                        <form method="POST" action="{{ route('kharch.destroy',$e) }}" onsubmit="return confirm('{{ __('kharch.delete_confirm') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                        @else
                        <span class="text-muted" style="font-size:11px;">Daily Report</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; color:#9ca3af; padding:30px;">{{ __('kharch.no_expense') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
    <div style="padding:12px 16px;">{{ $expenses->links() }}</div>
</x-form-card>
@endsection