@php
    $isEdit = isset($asset) && $asset->exists;
@endphp

<div class="grid-3">
    @if(!$isEdit)
    <div class="form-group">
        <label class="form-label">{{ __('asset.asset_code') }}</label>
        <input type="text" class="form-control" value="{{ $nextCode ?? '' }}" readonly>
    </div>
    @else
    <div class="form-group">
        <label class="form-label">{{ __('asset.asset_code') }}</label>
        <input type="text" class="form-control" value="{{ $asset->asset_code }}" readonly>
    </div>
    @endif

    <div class="form-group">
        <label class="form-label">{{ __('asset.asset_name') }} *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $asset->name ?? '') }}" required>
    </div>

    <div class="form-group">
        <label class="form-label">{{ __('asset.category') }} *</label>
        <select name="category" class="form-control" required>
            @foreach($categories as $key => $label)
            <option value="{{ $key }}" @selected(old('category', $asset->category ?? 'tractor') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid-3">
    <div class="form-group">
        <label class="form-label">{{ __('asset.purchase_date') }}</label>
        <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', optional($asset->purchase_date)->format('Y-m-d')) }}">
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('asset.purchase_price') }}</label>
        <input type="number" step="0.01" min="0" name="purchase_cost" class="form-control" value="{{ old('purchase_cost', $asset->purchase_cost ?? '') }}">
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('asset.current_value') }}</label>
        <input type="number" step="0.01" min="0" name="current_value" class="form-control" value="{{ old('current_value', $asset->current_value ?? '') }}">
    </div>
</div>

<div class="grid-3">
    <div class="form-group">
        <label class="form-label">{{ __('asset.vendor_name') }}</label>
        <input type="text" name="vendor_name" class="form-control" value="{{ old('vendor_name', $asset->vendor_name ?? '') }}">
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('asset.vendor_mobile') }}</label>
        <input type="text" name="vendor_mobile" class="form-control" value="{{ old('vendor_mobile', $asset->vendor_mobile ?? '') }}">
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('asset.warranty_months') }}</label>
        <input type="number" min="0" name="warranty_months" class="form-control" value="{{ old('warranty_months', $asset->warranty_months ?? '') }}">
    </div>
</div>

<div class="grid-3">
    <div class="form-group">
        <label class="form-label">{{ __('asset.status') }} *</label>
        <select name="status" class="form-control" required>
            @foreach($statuses as $st)
            <option value="{{ $st }}" @selected(old('status', $asset->status ?? 'active') === $st)>{{ __('asset.' . $st) }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('asset.image') }}</label>
        <input type="file" name="image" class="form-control" accept="image/*">
    </div>
    @if($isEdit && $asset->image)
    <div class="form-group">
        <label class="form-label">{{ __('asset.current_image') }}</label>
        <img src="{{ asset('storage/'.$asset->image) }}" alt="" style="max-width:80px;border-radius:8px;">
    </div>
    @endif
</div>

<div class="form-group">
    <label class="form-label">{{ __('asset.notes') }}</label>
    <textarea name="notes" rows="3" class="form-control" placeholder="{{ __('asset.notes_placeholder') }}">{{ old('notes', $asset->notes ?? $asset->description ?? '') }}</textarea>
</div>
