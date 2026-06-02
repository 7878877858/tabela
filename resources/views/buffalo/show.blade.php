@extends('layouts.app')
@section('title', __('buffalo.title').' — '.$buffalo->tag_number)

@section('content')
<div class="page-header">
    <h2>🐃 {{ $buffalo->tag_number }} {{ $buffalo->name ? '— '.$buffalo->name : '' }}</h2>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('buffalo.edit',$buffalo) }}" class="btn btn-outline btn-sm">✏️ {{ __('buffalo.edit_buffalo') }}</a>
        <a href="{{ route('buffalo.index') }}" class="btn btn-ghost btn-sm">← {{ __('buffalo.back') }}</a>
    </div>
</div>

<div class="grid-2" style="margin-bottom:20px;">
    <div class="card">
        <h3 style="font-size:14px; font-weight:600; margin-bottom:12px; color:#6b7280;">📋 {{ __('buffalo.details') }}</h3>
        <table style="font-size:14px;">
            <tr><td style="color:#6b7280; padding:5px 0; width:130px;">{{ __('buffalo.status') }}</td><td><span class="badge {{ $buffalo->status==='active' ? 'badge-green' : 'badge-red' }}">{{ $buffalo->status_label }}</span></td></tr>
            <tr><td style="color:#6b7280; padding:5px 0;">{{ __('buffalo.milk') }}</td><td><span class="badge badge-blue">{{ $buffalo->lactation_label }}</span></td></tr>
            <tr><td style="color:#6b7280; padding:5px 0;">{{ __('buffalo.dob') }}</td><td>{{ $buffalo->dob?->format('d/m/Y') ?? '—' }}</td></tr>
            <tr><td style="color:#6b7280; padding:5px 0;">{{ __('buffalo.purchase_date') }}</td><td>{{ $buffalo->purchase_date?->format('d/m/Y') ?? '—' }}</td></tr>
            <tr><td style="color:#6b7280; padding:5px 0;">{{ __('buffalo.purchase_price') }}</td><td>{{ $buffalo->purchase_price ? '₹'.number_format($buffalo->purchase_price,0) : '—' }}</td></tr>
        </table>
    </div>

    <div class="card">
        <h3 style="font-size:14px; font-weight:600; margin-bottom:12px; color:#6b7280;">📊 {{ __('buffalo.production') }}</h3>
        <div style="display:flex; flex-direction:column; gap:8px;">
            @foreach($monthlyMilk as $row)
            <div style="display:flex; justify-content:space-between; font-size:14px; padding:6px 0; border-bottom:1px solid #f3f4f6;">
                <span>{{ \Carbon\Carbon::create($row->yr, $row->mo)->format('M Y') }}</span>
                <strong>{{ number_format($row->total,1) }} L</strong>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Recent milk --}}
<div class="card" style="margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">🥛 {{ __('buffalo.recent_milk_entry') }}</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('buffalo.date') }}</th><th>{{ __('buffalo.morning') }} (L)</th><th>{{ __('buffalo.evening') }} (L)</th><th>{{ __('buffalo.total') }} (L)</th></tr></thead>
            <tbody>
                @forelse($milkHistory as $e)
                <tr>
                    <td>{{ $e->entry_date->format('d/m/Y') }}</td>
                    <td>{{ number_format($e->morning_liters,1) }}</td>
                    <td>{{ number_format($e->evening_liters,1) }}</td>
                    <td><strong>{{ number_format($e->total_liters,1) }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; color:#9ca3af;">{{ __('buffalo.no_entry') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $milkHistory->links() }}</div>
</div>
@endsection