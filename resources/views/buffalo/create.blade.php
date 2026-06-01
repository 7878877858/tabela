@extends('layouts.app')
@section('title', isset($buffalo) ? 'ભેંસ સુધારો' : 'નવી ભેંસ')

@section('content')
<div class="page-header">
    <h2>{{ isset($buffalo) ? '✏️ ભેંસ સુધારો — '.$buffalo->tag_number : '➕ નવી ભેંસ ઉમેરો' }}</h2>
    <a href="{{ route('buffalo.index') }}" class="btn btn-ghost btn-sm">← પાછા</a>
</div>

<div class="card" style="max-width:640px;">
    <form method="POST" action="{{ isset($buffalo) ? route('buffalo.update',$buffalo) : route('buffalo.store') }}">
        @csrf
        @if(isset($buffalo)) @method('PUT') @endif

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">ટેગ નંબર *</label>
                <input type="text" name="tag_number" class="form-control"
                    value="{{ old('tag_number', $buffalo->tag_number ?? '') }}"
                    placeholder="દા.ત. B001" required>
            </div>
            <div class="form-group">
                <label class="form-label">નામ (ઐચ્છિક)</label>
                <input type="text" name="name" class="form-control"
                    value="{{ old('name', $buffalo->name ?? '') }}" placeholder="દા.ત. ગૌરી">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">સ્થિતિ</label>
                <select name="status" class="form-control">
                    @foreach(['active'=>'સક્રિય','dry'=>'સૂકી','sold'=>'વેચાઈ','dead'=>'મૃત'] as $v => $l)
                    <option value="{{ $v }}" {{ old('status', $buffalo->status ?? 'active') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">દૂધ સ્થિતિ</label>
                <select name="lactation_status" class="form-control">
                    <option value="lactating" {{ old('lactation_status', $buffalo->lactation_status ?? '') === 'lactating' ? 'selected' : '' }}>🥛 દૂધ આપે</option>
                    <option value="dry" {{ old('lactation_status', $buffalo->lactation_status ?? 'dry') === 'dry' ? 'selected' : '' }}>🌵 સૂકી</option>
                    <option value="pregnant" {{ old('lactation_status', $buffalo->lactation_status ?? '') === 'pregnant' ? 'selected' : '' }}>🤰 ગર્ભવતી</option>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">જન્મ તારીખ</label>
                <input type="date" name="dob" class="form-control"
                    value="{{ old('dob', isset($buffalo) ? $buffalo->dob?->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group">
                <label class="form-label">ખરીદ તારીખ</label>
                <input type="date" name="purchase_date" class="form-control"
                    value="{{ old('purchase_date', isset($buffalo) ? $buffalo->purchase_date?->format('Y-m-d') : '') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">ખરીદ કિંમત (₹)</label>
            <input type="number" name="purchase_price" step="0.01" class="form-control"
                value="{{ old('purchase_price', $buffalo->purchase_price ?? '') }}" placeholder="0.00">
        </div>

        <div class="form-group">
            <label class="form-label">નોંધ</label>
            <textarea name="notes" class="form-control" rows="3"
                placeholder="ઐચ્છિક">{{ old('notes', $buffalo->notes ?? '') }}</textarea>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">
                {{ isset($buffalo) ? '💾 અપડેટ' : '➕ ઉમેરો' }}
            </button>
            <a href="{{ route('buffalo.index') }}" class="btn btn-ghost">રદ</a>
        </div>
    </form>
</div>
@endsection