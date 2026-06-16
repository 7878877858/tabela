@extends('layouts.app')
@section('title', isset($buffalo) ? __('buffalo.edit_buffalo') : __('buffalo.new_buffalo'))
<style>
    .buffalo-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        width: 100%;
    }

    .buffalo-layout>.card {
        width: 100%;
        max-width: none !important;
    }

    .card {
        width: 100%;
    }

    @media(max-width:992px) {
        .buffalo-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@section('content')
<div class="page-header">
    <h2>{{ isset($buffalo) ? '✏️ '.__('buffalo.edit_buffalo').' — '.$buffalo->tag_number : '➕ '.__('buffalo.new_buffalo') }}</h2>
    <a href="{{ route('buffalo.index') }}" class="btn btn-ghost btn-sm">← {{ __('buffalo.back') }}</a>
</div>

<form class="buffalo-layout" method="POST" action="{{ isset($buffalo) ? route('buffalo.update',$buffalo) : route('buffalo.store') }}">

    <div class="card" style="max-width:640px;">

        @csrf
        @if(isset($buffalo)) @method('PUT') @endif

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.tag_number') }} *</label>
                <input type="text" name="tag_number" class="form-control" value="{{ old('tag_number', $buffalo->tag_number ?? '') }}" placeholder="{{ __('buffalo.tag_number_placeholder') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.optional_name') }}</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $buffalo->name ?? '') }}" placeholder="{{ __('buffalo.name_placeholder') }}">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.status') }}</label>
                <select name="status" class="form-control">
                    @foreach(['active' => __('buffalo.active'),'dry' => __('buffalo.dry'),'sold' => __('buffalo.sold'),'dead' => __('buffalo.dead')] as $v => $l)
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
                <input type="date" name="dob" class="form-control" value="{{ old('dob', isset($buffalo) ? $buffalo->dob?->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.purchase_date') }}</label>
                <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', isset($buffalo) ? $buffalo->purchase_date?->format('Y-m-d') : '') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('buffalo.purchase_price') }}</label>
            <input type="number" name="purchase_price" step="0.01" placeholder="0.00" class="form-control" value="{{ old('purchase_price', $buffalo->purchase_price ?? '')}}">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('buffalo.notes') }}</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="{{ __('buffalo.notes_placeholder') }}">{{ old('notes', $buffalo->notes ?? '') }}</textarea>
        </div>


    </div>

    <div class="card" style="max-width:640px;">
        <h3>🤰 પ્રજનન માહિતી</h3>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Heat તારીખ</label>
                <input type="date" name="heat_date" class="form-control" value="{{ old('heat_date', isset($buffalo) ? $buffalo->heat_date : '') }}">
            </div>

            <div class="form-group">
                <label class="form-label">AI તારીખ</label>
                <input type="date" name="ai_date" class="form-control" value="{{ old('ai_date', isset($buffalo) ? $buffalo->ai_date : '') }}">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Pregnancy Check તારીખ</label>
                <input type="date"
                    name="pregnancy_check_date"
                    class="form-control"
                    value="{{ old('pregnancy_check_date', isset($buffalo) ? $buffalo->pregnancy_check_date : '') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Expected Delivery તારીખ</label>
                <input type="date"
                    name="expected_delivery_date"
                    class="form-control"
                    value="{{ old('expected_delivery_date', isset($buffalo) ? $buffalo->expected_delivery_date : '') }}">
            </div>
        </div>
        <h3>🐄 બચ્ચા જન્મ માહિતી</h3>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Birth તારીખ</label>
                <input type="date"
                    name="birth_date"
                    class="form-control"
                    value="{{ old('birth_date', isset($buffalo) ? $buffalo->birth_date : '') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Calf Tag Number</label>
                <input type="text"
                    name="calf_tag_number"
                    class="form-control"
                    value="{{ old('calf_tag_number', $buffalo->calf_tag_number ?? '') }}">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Calf Gender</label>
                <select name="calf_gender" class="form-control">
                    <option value="">Select Gender</option>
                    <option value="male"
                        {{ old('calf_gender', $buffalo->calf_gender ?? '') == 'male' ? 'selected' : '' }}>
                        Male
                    </option>
                    <option value="female"
                        {{ old('calf_gender', $buffalo->calf_gender ?? '') == 'female' ? 'selected' : '' }}>
                        Female
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Calf Weight (Kg)</label>
                <input type="number"
                    step="0.01" name="calf_weight" class="form-control" value="{{ old('calf_weight', $buffalo->calf_weight ?? '') }}">
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            {{ isset($buffalo) ? '💾 '.__('buffalo.update') : '➕ '.__('buffalo.save') }}
        </button>
        <a href="{{ route('buffalo.index') }}" class="btn btn-ghost">{{ __('buffalo.cancel') }}</a>
    </div>
</form>

@endsection