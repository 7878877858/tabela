@props([
    'name',
    'value' => null,
    'class' => '',
    'required' => false,
    'placeholder' => 'ટેગ નંબર અથવા પશુ નામ શોધો...',
    'animals' => null,
    'filterIds' => null,
    'optionsFrom' => 'registry',
    'labelMode' => 'full',
])

@php
    $animalList = $animals ?? ($buffaloes ?? collect());
    if ($filterIds !== null) {
        $filterIds = is_array($filterIds) ? $filterIds : [$filterIds];
        $animalList = $animalList->whereIn('id', $filterIds);
    }
    $selectPlaceholder = match ($labelMode) {
        'tag', 'tag-name' => 'ટેગ / નામ શોધો...',
        default => $placeholder,
    };
@endphp

@php
    $optionLabel = function ($buffalo) use ($labelMode) {
        return match ($labelMode) {
            'tag' => $buffalo->tag_number,
            'tag-name' => $buffalo->tag_number . ($buffalo->name ? ' - ' . $buffalo->name : ''),
            default => $buffalo->display_label,
        };
    };
@endphp

<div class="animal-select-wrap">
    <select
        {{ $attributes->merge(['class' => 'animal-select ' . $class]) }}
        name="{{ $name }}"
        @if($required) required @endif
        data-placeholder="{{ $selectPlaceholder }}"
        data-options-from="{{ $optionsFrom }}"
        data-label-mode="{{ $labelMode }}"
    >
        <option value="">{{ $selectPlaceholder }}</option>
        @foreach($animalList as $buffalo)
            <option
                value="{{ $buffalo->id }}"
                data-animal-type="{{ $buffalo->animal_type ?? 'buffalo' }}"
                data-name="{{ $buffalo->name ?? '' }}"
                data-tag="{{ $buffalo->tag_number ?? '' }}"
                @selected((string) $value === (string) $buffalo->id)
            >{{ $optionLabel($buffalo) }}</option>
        @endforeach
    </select>
</div>
