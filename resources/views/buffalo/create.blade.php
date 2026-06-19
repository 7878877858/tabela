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

<x-section-header :title="isset($buffalo) ? __('buffalo.edit_buffalo') . ' — ' . $buffalo->tag_number : __('buffalo.new_buffalo')" :icon="isset($buffalo) ? '✏️' : '➕'">
    <x-slot:actions>
        <a href="{{ route('buffalo.index') }}" class="btn btn-ghost btn-sm">← {{ __('buffalo.back') }}</a>
    </x-slot:actions>
</x-section-header>

<form class="buffalo-layout" method="POST" action="{{ isset($buffalo) ? route('buffalo.update',$buffalo) : route('buffalo.store') }}">

    <x-form-card :title="isset($buffalo) ? __('buffalo.edit_buffalo') : __('buffalo.new_buffalo')" icon="🐃">

        @csrf
        @if(isset($buffalo)) @method('PUT') @endif

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">પશુ પ્રકાર *</label>
                @if(isset($buffalo))
                    <input type="text" class="form-control" value="{{ $buffalo->animal_type_label }}" readonly>
                @else
                    <select name="animal_type" id="animalTypeSelect" class="form-control" required>
                        @foreach(\App\Models\Buffalo::animalTypeOptions() as $value => $label)
                        <option value="{{ $value }}" {{ old('animal_type', 'buffalo') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.tag_number') }}</label>
                @if(isset($buffalo))
                    <input type="text" class="form-control" value="{{ $buffalo->tag_number }}" readonly>
                @else
                    <input type="text" id="tagPreview" class="form-control" value="{{ ($nextTags ?? [])[old('animal_type', 'buffalo')] ?? 'B001' }}" readonly>
                    <small style="color:#6b7280;">ટેગ આપમેળે જનરેટ થશે</small>
                @endif
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('buffalo.optional_name') }}</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $buffalo->name ?? '') }}" placeholder="{{ __('buffalo.name_placeholder') }}">
            </div>
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


    </x-form-card>

    <x-form-card title="પ્રજનન માહિતી" icon="🤰">
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
        @if(!isset($buffalo) || in_array($buffalo->normalized_animal_type, ['buffalo', 'cow']))
        <h3>🐄 બચ્ચા જન્મ માહિતી</h3>
        @php
            $linkedCalf = isset($buffalo) ? $buffalo->birthCalf : null;
            $birthDateValue = old('birth_date', isset($buffalo) ? ($linkedCalf?->birth_date?->format('Y-m-d') ?? $buffalo->birth_date?->format('Y-m-d')) : '');
            $calfGenderValue = old('calf_gender', isset($buffalo) ? ($linkedCalf?->gender ?? $buffalo->calf_gender) : '');
            $calfWeightValue = old('calf_weight', isset($buffalo) ? ($linkedCalf?->weight ?? $buffalo->calf_weight) : '');
        @endphp
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Birth તારીખ</label>
                <input type="date"
                    name="birth_date"
                    class="form-control"
                    value="{{ $birthDateValue }}">
            </div>

            <div class="form-group">
                <label class="form-label">બચ્ચા ટેગ</label>
                @if($linkedCalf)
                    <input type="text" class="form-control" value="{{ $linkedCalf->tag_number }}" readonly>
                    <small style="color:#6b7280;display:block;margin-top:6px;">
                        <a href="{{ route('buffalo.show', $linkedCalf) }}">બચ્ચા પ્રોફાઇલ જુઓ</a>
                    </small>
                @else
                    <input type="text" class="form-control" value="સેવ પછી BC/CC ટેગ આપમેળે બનશે" readonly>
                    <small style="color:#6b7280;display:block;margin-top:6px;">
                        જન્મ તારીખ ભરો — ભેંસ માટે BC001, ગાય માટે CC001 ટેગ આપમેળે મળશે.
                    </small>
                @endif
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Calf Gender</label>
                <select name="calf_gender" class="form-control">
                    <option value="">Select Gender</option>
                    <option value="male"
                        {{ $calfGenderValue == 'male' ? 'selected' : '' }}>
                        Male
                    </option>
                    <option value="female"
                        {{ $calfGenderValue == 'female' ? 'selected' : '' }}>
                        Female
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Calf Weight (Kg)</label>
                <input type="number"
                    step="0.01" name="calf_weight" class="form-control" value="{{ $calfWeightValue }}">
            </div>
        </div>
        @endif
    </x-form-card>
    <div class="form-actions" style="grid-column:1/-1;display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;">
        <button type="submit" class="btn btn-primary">
            {{ isset($buffalo) ? '💾 '.__('buffalo.update') : '➕ '.__('buffalo.save') }}
        </button>
        <a href="{{ route('buffalo.index') }}" class="btn btn-ghost">{{ __('buffalo.cancel') }}</a>
    </div>
</form>

@if(!isset($buffalo))
@push('scripts')
<script>
(function () {
    const select = document.getElementById('animalTypeSelect');
    const preview = document.getElementById('tagPreview');
    if (!select || !preview) return;

    const nextTags = @json($nextTags ?? []);

    function updatePreview(type) {
        preview.value = nextTags[type] || '—';
    }

    select.addEventListener('change', function () {
        fetch(`{{ route('buffalo.next-tag') }}?animal_type=${encodeURIComponent(this.value)}`)
            .then(r => r.json())
            .then(data => { preview.value = data.tag_number || '—'; })
            .catch(() => updatePreview(this.value));
    });
})();
</script>
@endpush
@endif

@endsection