@extends('layouts.app')
@section('title', $data['title'] ?? 'Report')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2>{{ $data['title'] ?? 'Report' }}</h2>
        <p class="text-muted mb-0" style="font-size:13px;">{{ $filters['from_date'] }} — {{ $filters['to_date'] }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.generator') }}" class="btn btn-outline btn-sm">← Back</a>
        <a href="{{ route('reports.pdf', $filters) }}" class="btn btn-primary btn-sm" target="_blank">Print / PDF</a>
    </div>
</div>

@include('reports.partials._dynamic_body', ['data' => $data, 'filters' => $filters])
@endsection
