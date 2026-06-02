@extends('layouts.app')
@section('title', isset($buffalo) ? __('buffalo.edit_buffalo') : __('buffalo.new_buffalo'))

@section('content')
<div class="page-header">
    <h2>{{ isset($buffalo) ? '✏️ '.__('buffalo.edit_buffalo').' — '.$buffalo->tag_number : '➕ '.__('buffalo.new_buffalo') }}</h2>
    <a href="{{ route('buffalo.index') }}" class="btn btn-ghost btn-sm">← {{ __('buffalo.back') }}</a>
</div>

<div class="card" style="max-width:640px;">
    <form method="POST" action="{{ isset($buffalo) ? route('buffalo.update',$buffalo) : route('buffalo.store') }}">
        @csrf
        @if(isset($buffalo)) @method('PUT') @endif

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.tag_number') }} *</label>
                <input type="text" name="tag_number" class="form-control"
                    value="{{ old('tag_number', $buffalo->tag_number ?? '') }}"
                    placeholder="{{ __('buffalo.tag_number_placeholder') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.optional_name') }}</label>
                <input type="text" name="name" class="form-control"
                    value="{{ old('name', $buffalo->name ?? '') }}" placeholder="{{ __('buffalo.name_placeholder') }}">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.status') }}</label>
                <select name="status" class="form-control">
                    @foreach([
                                'active' => __('buffalo.active'),
                                'dry' => __('buffalo.dry'),
                                'sold' => __('buffalo.sold'),
                                'dead' => __('buffalo.dead')
                            ] as $v => $l)
                    <option value="{{ $v }}" {{ old('status', $buffalo->status ?? 'active') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.milk_status') }}</label>
                <select name="lactation_status" class="form-control">
                    <option value="lactating" {{ old('lactation_status', $buffalo->lactation_status ?? '') === 'lactating' ? 'selected' : '' }}>🥛 {{ __('buffalo.lactating') }}</option>
                    <option value="dry" {{ old('lactation_status', $buffalo->lactation_status ?? 'dry') === 'dry' ? 'selected' : '' }}>🌵 {{ __('buffalo.dry') }}</option>
                    <option value="pregnant" {{ old('lactation_status', $buffalo->lactation_status ?? '') === 'pregnant' ? 'selected' : '' }}>🤰 {{ __('buffalo.pregnant') }}</option>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.dob') }}</label>
                <input type="date" name="dob" class="form-control"
                    value="{{ old('dob', isset($buffalo) ? $buffalo->dob?->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.purchase_date') }}</label>
                <input type="date" name="purchase_date" class="form-control"
                    value="{{ old('purchase_date', isset($buffalo) ? $buffalo->purchase_date?->format('Y-m-d') : '') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('buffalo.purchase_price') }}</label>
            <input type="number" name="purchase_price" step="0.01" class="form-control"
                value="{{ old('purchase_price', $buffalo->purchase_price ?? '') }}" placeholder="0.00">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('buffalo.notes') }}</label>
            <textarea name="notes" class="form-control" rows="3"
                placeholder="{{ __('buffalo.notes_placeholder') }}">{{ old('notes', $buffalo->notes ?? '') }}</textarea>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">
                {{ isset($buffalo) ? '💾 '.__('buffalo.update') : '➕ '.__('buffalo.save') }}
            </button>
            <a href="{{ route('buffalo.index') }}" class="btn btn-ghost">{{ __('buffalo.cancel') }}</a>
        </div>
    </form>
</div>
@endsection