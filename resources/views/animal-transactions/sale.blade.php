@extends('layouts.app')
@section('title', __('farm.sell_animal'))

@section('content')

<x-section-header :title="__('farm.sell_animal')" icon="💰">
    <x-slot:actions>
        <a href="{{ route('animal-transactions.index') }}" class="btn btn-ghost btn-sm">← {{ __('farm.transaction_history') }}</a>
    </x-slot:actions>
</x-section-header>

@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="alert alert-warning">⚠️ વેચાણ પછી પશુ સક્રિય યાદીઓમાં દેખાશે નહીં (દૂધ, ચારો, આરોગ્ય, વેક્સિન, ગર્ભાવસ્થા).</div>

<x-form-card :title="__('farm.sell_animal')" icon="➕">
    <form method="POST" action="{{ route('animal-transactions.sale.store') }}" class="grid-2">
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('farm.animal') }} *</label>
            <select name="buffalo_id" class="form-control" required>
                <option value="">— પસંદ કરો —</option>
                @foreach($animals as $animal)
                <option value="{{ $animal->id }}" @selected(old('buffalo_id') == $animal->id)>{{ $animal->display_label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.sale_price') }} *</label>
            <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount') }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.buyer') }}</label>
            <input type="text" name="counterparty_name" class="form-control" value="{{ old('counterparty_name') }}">
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('farm.date') }} *</label>
            <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', today()->toDateString()) }}" required>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label">{{ __('farm.remarks') }}</label>
            <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
        </div>
        <div><button type="submit" class="btn btn-primary">{{ __('farm.add') }}</button></div>
    </form>
</x-form-card>
@endsection
