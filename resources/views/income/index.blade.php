@extends('layouts.app')
@section('title', __('income.income'))

@section('content')

<x-section-header :title="__('income.income')" icon="📈" />

<div class="alert alert-warning">
    📋 <strong>Read-only report view</strong> — income syncs from <a href="{{ route('daily-reports.create') }}">Daily Report</a>.
</div>

@if(false)
<div class="card" style="margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">{{ __('income.add_income') }}</h3>
    <form method="POST" action="{{ route('income.store') }}">
        @csrf
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">{{ __('income.date') }} *</label>
                <input type="date" name="income_date" class="form-control" value="{{ old('income_date', today()->toDateString()) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('income.category') }} *</label>
                <select name="category" class="form-control" required>
                    <option value="milk_sale">🥛 {{ __('income.milk_sale') }}</option>
                    <option value="animal_sale">🐃 {{ __('income.animal_sale') }}</option>
                    <option value="calf_sale">🐄 {{ __('income.calf_sale') }}</option>
                    <option value="government_subsidy">🏛️ {{ __('income.government_subsidy') }}</option>
                    <option value="breeding_income">🤝 {{ __('income.breeding_income') }}</option>
                    <option value="manure_sale">🌱 {{ __('income.manure_sale') }}</option>
                    <option value="other_income">📌 {{ __('income.other_income') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('income.amount') }} *</label>
                <input type="number" name="amount" step="0.01" min="0" class="form-control" placeholder="0.00" value="{{ old('amount') }}" required>
            </div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('income.description') }} *</label>
                <input type="text" name="description" class="form-control" placeholder="{{ __('income.description_placeholder') }}" value="{{ old('description') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('income.buffalo') }} ({{ __('income.optional') }})</label>
                <select name="buffalo_id" class="form-control">
                    <option value="">— {{ __('income.all_buffaloes') }} —</option>
                    @foreach($buffaloes as $b)
                    <option value="{{ $b->id }}" {{ old('buffalo_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->display_label }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">➕ {{ __('income.add') }}</button>
    </form>
</div>
@endif

<form method="GET" style="display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap;">
    <select name="month" class="form-control" style="width:130px;" onchange="this.form.submit()">
        @foreach(range(1, 12) as $m)
        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->locale(app()->getLocale())->translatedFormat('F') }}
        </option>
        @endforeach
    </select>
    <select name="year" class="form-control" style="width:100px;" onchange="this.form.submit()">
        @foreach(range(now()->year, 2020) as $y)
        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
    </select>
</form>

<div class="summary-row">
    <span>
        📅 {{ \Carbon\Carbon::create()->month($month)->locale(app()->getLocale())->translatedFormat('F') }}
        {{ $year }}
    </span>
    <span>{{ __('income.total_income') }}: <strong>₹{{ number_format($total, 0) }}</strong></span>
    <span>📊 {{ __('income.entries') }}: <strong>{{ $incomeCount }}</strong></span>
    @foreach($byCategory as $cat)
    <span>
        {{ match($cat->category) {
            'milk_sale' => '🥛 '.__('income.milk_sale'),
            'animal_sale' => '🐃 '.__('income.animal_sale'),
            'calf_sale' => '🐄 '.__('income.calf_sale'),
            'government_subsidy' => '🏛️ '.__('income.government_subsidy'),
            'breeding_income' => '🤝 '.__('income.breeding_income'),
            'manure_sale' => '🌱 '.__('income.manure_sale'),
            default => '📌 '.__('income.other_income'),
        } }}: <strong>₹{{ number_format($cat->total, 0) }}</strong>
    </span>
    @endforeach
</div>

<x-form-card :title="__('income.income')" icon="📋" :flush="true">
    <x-responsive-table>
        <table class="ds-table">
            <thead>
                <tr>
                    <th>{{ __('income.date') }}</th>
                    <th>{{ __('income.category') }}</th>
                    <th>{{ __('income.description') }}</th>
                    <th>{{ __('income.buffalo') }}</th>
                    <th>{{ __('income.amount') }} (₹)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($incomes as $income)
                <tr>
                    <td>{{ $income->income_date->format('d/m/Y') }}</td>
                    <td><span class="badge badge-blue">{{ $income->category_label }}</span></td>
                    <td>{{ $income->description }}</td>
                    <td>{{ $income->buffalo?->tag_number ?? '—' }}</td>
                    <td><strong>₹{{ number_format($income->amount, 0) }}</strong></td>
                    <td>
                        @if(!$income->daily_report_id)
                        <form method="POST" action="{{ route('income.destroy', $income) }}" onsubmit="return confirm('{{ __('income.delete_confirm') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                        @else
                        <span class="text-muted" style="font-size:11px;">Daily Report</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#9ca3af; padding:30px;">{{ __('income.no_income') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-table>
    <div style="padding:12px 16px;">{{ $incomes->links() }}</div>
</x-form-card>
@endsection
