@extends('layouts.app')

@section('title', __('asset.assets_management'))

@section('content')

<div class="page-header">
    <h2>🚜 {{ __('asset.assets_management') }}</h2>
</div>

{{-- Add Asset --}}
<div class="card" style="margin-bottom:20px;">
    <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">
        {{ isset($editAsset) ? '✏️ ' . __('asset.edit_asset') : '➕ ' . __('asset.add_new_asset') }}
    </h3>

        @if(isset($editAsset))
        <form method="POST" action="{{ route('assets.update',$editAsset) }}" enctype="multipart/form-data">
            @method('PUT')
        @else
        <form method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data">
        @endif

        @csrf

        <div class="grid-3">

            <div class="form-group">
                <label class="form-label">{{ __('asset.asset_name') }} *</label>
                <input type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name',$editAsset->name ?? '') }}"
                    placeholder="{{ __('asset.asset_name_placeholder') }}"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('asset.category') }}</label>
                <select name="category" class="form-control">
                    <option value="Vehicle">🚜 {{ __('asset.vehicle') }}</option>
                    <option value="Machine">⚙️ {{ __('asset.machine') }}</option>
                    <option value="Equipment">🛠 {{ __('asset.equipment') }}</option>
                    <option value="Other">📦 {{ __('asset.other') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('asset.quantity') }}</label>
                <input type="number"
                       name="quantity"
                       value="{{ old('quantity',$editAsset->quantity ?? 1) }}"
                       min="1"
                       class="form-control">
            </div>

        </div>

        <div class="grid-3">

            <div class="form-group">
                <label class="form-label">{{ __('asset.purchase_date') }}</label>
                <input type="date"
                    name="purchase_date"
                    value="{{ old('purchase_date',$editAsset->purchase_date ?? '') }}"
                    class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('asset.purchase_cost') }}</label>
                <input type="number"
                    name="purchase_cost"
                    value="{{ old('purchase_cost',$editAsset->purchase_cost ?? '') }}"
                    placeholder="{{ __('asset.purchase_cost_placeholder') }}"
                    class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('asset.current_value') }}</label>
                <input type="number"
                    name="current_value"
                    value="{{ old('current_value',$editAsset->current_value ?? '') }}"
                    placeholder="{{ __('asset.current_value_placeholder') }}"
                    class="form-control">
            </div>

        </div>

        <div class="grid-3">

            <div class="form-group">
                <label class="form-label">{{ __('asset.condition') }}</label>
                <select name="condition" class="form-control">
                    <option value="excellent">{{ __('asset.excellent') }}</option>
                    <option value="good">{{ __('asset.good') }}</option>
                    <option value="needs_repair">{{ __('asset.needs_repair') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('asset.status') }}</label>
                <select name="status" class="form-control">
                    <option value="active">{{ __('asset.active') }}</option>
                    <option value="sold">{{ __('asset.sold') }}</option>
                    <option value="scrap">{{ __('asset.scrap') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('asset.image') }}</label>
                <input type="file"
                       name="image"
                       class="form-control">
            </div>

        </div>

        <div class="form-group">
            <label class="form-label">{{ __('asset.description') }}</label>
           <textarea name="description"
            rows="3"
            class="form-control"
            placeholder="{{ __('asset.description_placeholder') }}">{{ old('description',$editAsset->description ?? '') }}</textarea>
        </div>

       <button type="submit" class="btn btn-primary">
            {{ isset($editAsset) ? '💾 ' . __('asset.update_asset') : '➕ ' . __('asset.add_asset') }}
        </button>

        @if(isset($editAsset))
        <a href="{{ route('assets.index') }}" class="btn btn-outline">
            {{ __('asset.cancel') }}
        </a>
@endif

    </form>
</div>

{{-- Asset List --}}
<div class="card">

    <h3 style="margin-bottom:15px;">
        📦 {{ __('asset.asset_list') }}
    </h3>

    <div class="table-wrap">

        <table>

            <thead>
            <tr>
                <th>{{ __('asset.image') }}</th>
                <th>{{ __('asset.asset') }}</th>
                <th>{{ __('asset.category') }}</th>
                <th>{{ __('asset.quantity') }}</th>
                <th>{{ __('asset.condition') }}</th>
                <th>{{ __('asset.status') }}</th>
                <th>{{ __('asset.cost') }}</th>
                <th>{{ __('asset.action') }}</th>
            </tr>
            </thead>

            <tbody>

            @forelse($assets as $asset)

                <tr>

                    <td>
                    @if($asset->image)
                        <img src="{{ asset('storage/'.$asset->image) }}"
                            alt="{{ $asset->name }}"
                            style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                    @else
                        {{ __('asset.no_image') }}
                    @endif

                    </td>
  
                    <td>
                        <strong>{{ $asset->name }}</strong>
                    </td>

                    <td>{{ $asset->category }}</td>

                    <td>{{ $asset->quantity }}</td>

                    <td>{{ ucfirst($asset->condition) }}</td>

                    <td>
                        <span class="badge badge-green">
                            {{ ucfirst($asset->status) }}
                        </span>
                    </td>

                    <td>
                        ₹{{ number_format($asset->purchase_cost,0) }}
                    </td>

                    <td>

                        <a href="{{ route('assets.edit',$asset) }}"
                           class="btn btn-outline btn-sm">
                            ✏️
                        </a>

                        <form method="POST"
                              action="{{ route('assets.destroy',$asset) }}"
                              style="display:inline;"
                              onsubmit="return confirm('Delete Asset?')">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-danger btn-sm">
                                🗑
                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="8"
                        style="text-align:center;padding:30px;color:#999;">
                        {{ __('asset.no_assets') }}
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection