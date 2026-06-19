@extends('layouts.app')
@section('title', __('asset.add_new_asset'))

@section('content')
<link rel="stylesheet" href="{{ asset('static/css/asset-management.css') }}">

<div class="am-page">
    <div class="page-header">
        <h2>➕ {{ __('asset.add_new_asset') }}</h2>
        <a href="{{ route('assets.index') }}" class="btn btn-outline btn-sm">← {{ __('asset.back') }}</a>
    </div>

    <div class="am-card">
        <form method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data">
            @csrf
            @include('assets.partials._form', ['asset' => $asset, 'categories' => $categories, 'statuses' => $statuses, 'nextCode' => $nextCode])
            <button type="submit" class="btn btn-primary">➕ {{ __('asset.add_asset') }}</button>
        </form>
    </div>
</div>
@endsection
