@extends('layouts.app')
@section('title', 'New Feed')

@section('content')

<x-section-header title="New Feed Type" icon="🌾" subtitle="Add feed master — stock via Add Stock or initial quantity">
    <x-slot:actions>
        <a href="{{ route('feeds.index') }}" class="btn btn-ghost">← Back</a>
    </x-slot:actions>
</x-section-header>

<x-form-card title="New Feed" icon="🌾">
    <form method="POST" action="{{ route('feeds.store') }}">
        @csrf
        <div class="ds-form-grid ds-form-grid-3">
            <div class="form-group">
                <label class="form-label">Feed Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Napier" required>
            </div>
            <div class="form-group">
                <label class="form-label">Unit</label>
                <select name="unit" class="form-control">
                    @foreach(['Kg','Gram','Liter','Bag','Packet','Bundle','Piece'] as $u)
                    <option value="{{ $u }}">{{ $u }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Low Stock Threshold</label>
                <input type="number" step="0.01" min="0" name="min_stock" class="form-control" value="0">
            </div>
            <div class="form-group">
                <label class="form-label">Initial Stock (creates IN transaction)</label>
                <input type="number" step="0.01" min="0" name="volume" class="form-control" placeholder="0">
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Description</label>
                <textarea name="description" rows="2" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-success">Save</button>
            <a href="{{ route('feeds.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</x-form-card>
@endsection
