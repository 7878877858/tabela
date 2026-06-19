@props([
    'saleAnimals',
    'selected' => null,
])

<select name="animal_sale_buffalo_id[]" class="form-control animal-sale-select">
    <option value="">પશુ પસંદ કરો</option>
    @foreach($saleAnimals as $buffalo)
        @php
            $type = \App\Models\Buffalo::normalizeAnimalType($buffalo->animal_type ?? 'buffalo');
        @endphp
        <option
            value="{{ $buffalo->id }}"
            data-animal-type="{{ $type }}"
            data-tag="{{ $buffalo->tag_number }}"
            data-name="{{ $buffalo->name ?? '' }}"
            @selected($selected !== null && (string) $selected === (string) $buffalo->id)
        >{{ $buffalo->tag_number }}{{ $buffalo->name ? ' - ' . $buffalo->name : '' }}</option>
    @endforeach
</select>
