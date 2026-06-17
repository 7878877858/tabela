@extends('layouts.app')
@section('title', 'Edit Feed')

@section('content')

<x-section-header :title="'Edit — ' . $feed->name" icon="✏️" :subtitle="'Available: ' . number_format($feed->available_quantity, 2) . ' ' . $feed->unit">
    <x-slot:actions>
        <a href="{{ route('feeds.index') }}" class="btn btn-ghost">← Back</a>
    </x-slot:actions>
</x-section-header>

<x-form-card title="Edit Feed Master" icon="🌾">
    <form method="POST" action="{{ route('feeds.update', $feed->id) }}">
        @csrf @method('PUT')
        <div class="ds-form-grid ds-form-grid-3">
            <div class="form-group">
                <label class="form-label">Feed Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $feed->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Unit</label>
                <select name="unit" class="form-control">
                    @foreach(['Kg','Gram','Liter','Bag','Packet','Bundle','Piece'] as $u)
                    <option value="{{ $u }}" {{ $feed->unit == $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Low Stock Threshold</label>
                <input type="number" step="0.01" min="0" name="min_stock" class="form-control" value="{{ old('min_stock', $feed->min_stock) }}">
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $feed->description) }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="1" {{ $feed->status == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $feed->status == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <p class="text-muted" style="font-size:0.8125rem;">Stock is managed via <a href="{{ route('feeds.show', $feed) }}">Add Stock</a> and Daily Report consumption.</p>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</x-form-card>
@endsection
