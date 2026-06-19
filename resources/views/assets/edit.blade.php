@extends('layouts.app')
@section('title', __('asset.edit_asset'))

@section('content')
<link rel="stylesheet" href="{{ asset('static/css/asset-management.css') }}">

<div class="am-page">
    <div class="page-header">
        <h2>✏️ {{ __('asset.edit_asset') }}</h2>
        <a href="{{ route('assets.show', $asset) }}" class="btn btn-outline btn-sm">← {{ __('asset.back') }}</a>
    </div>

    <div class="am-card">
        <form method="POST" action="{{ route('assets.update', $asset) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('assets.partials._form', ['asset' => $asset, 'categories' => $categories, 'statuses' => $statuses])
            <button type="submit" class="btn btn-primary">💾 {{ __('asset.update_asset') }}</button>
        </form>
    </div>
</div>
@endsection
