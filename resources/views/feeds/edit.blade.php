@extends('layouts.app')

@section('title', 'Edit Feed')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">✏️ Feed Edit</h4>
        <small class="text-muted">
            ફીડ માહિતી અપડેટ કરો
        </small>
    </div>

    <a href="{{ route('feeds.index') }}"
        class="btn btn-outline-secondary">
        ← પાછા
    </a>
</div>

<div class="card shadow-sm border-0">

    <div class="card-header bg-warning">
        ✏️ ફીડ સંપાદિત કરો
    </div>

    <div class="card-body">

        <form method="POST"
            action="{{ route('feeds.update', $feed->id) }}">
            @csrf
            @method('PUT')

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        ફીડ નામ *
                    </label>

                    <input type="text"
                        name="name"
                        class="form-control"
                        value="{{ old('name', $feed->name) }}"
                        required>
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            ફીડ નામ *
                        </label>

                        <input type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $feed->name) }}"
                            required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            ઉપલબ્ધ સ્ટોક
                        </label>

                        <input type="number"
                            step="0.01"
                            name="volume"
                            class="form-control"
                            value="{{ old('volume', $feed->volume) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            એકમ (Unit)
                        </label>

                        <select name="unit" class="form-control">

                            <option value="Kg" {{ $feed->unit == 'Kg' ? 'selected' : '' }}>Kg</option>
                            <option value="Gram" {{ $feed->unit == 'Gram' ? 'selected' : '' }}>Gram</option>
                            <option value="Liter" {{ $feed->unit == 'Liter' ? 'selected' : '' }}>Liter</option>
                            <option value="Bag" {{ $feed->unit == 'Bag' ? 'selected' : '' }}>Bag</option>
                            <option value="Packet" {{ $feed->unit == 'Packet' ? 'selected' : '' }}>Packet</option>

                        </select>

                    </div>

                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">
                        વર્ણન
                    </label>

                    <textarea name="description"
                        rows="4"
                        class="form-control">{{ old('description', $feed->description) }}</textarea>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">
                        સ્થિતિ
                    </label>

                    <select name="status"
                        class="form-control">

                        <option value="1"
                            {{ $feed->status == 1 ? 'selected' : '' }}>
                            Active
                        </option>

                        <option value="0"
                            {{ $feed->status == 0 ? 'selected' : '' }}>
                            Inactive
                        </option>

                    </select>
                </div>

            </div>

            <hr>

            <button type="submit"
                class="btn btn-warning">
                <i class="fa fa-save"></i>
                Update
            </button>

            <a href="{{ route('feeds.index') }}"
                class="btn btn-light">
                Cancel
            </a>

        </form>

    </div>

</div>

@endsection