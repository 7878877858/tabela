@extends('layouts.app')
@section('title', __('milk.milk_history'))

@section('content')

<div class="page-header">
    <h2>📋 {{ __('milk.milk_history') }}</h2>
    <a href="{{ route('milk.index') }}" class="btn btn-outline btn-sm">➕ {{ __('milk.milk_entry') }}</a>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px; padding:14px 20px;">
    <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
        
        <label>{{ __('milk.month') }}:</label>
        <select name="month" onchange="this.form.submit()" class="form-control" style="width:120px;">
            @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                    {{ $m }}
                </option>
            @endfor
        </select>

        <label>{{ __('milk.year') }}:</label>
        <input type="number" name="year" value="{{ $year }}" 
            class="form-control" style="width:100px;" onchange="this.form.submit()">

        <span style="margin-left:auto; font-size:14px;">
            {{ __('milk.total') }} {{ __('milk.milk') }}: 
            <strong style="color:var(--primary);">
                {{ number_format($monthTotal,1) }} L
            </strong>
        </span>

    </form>
</div>

{{-- Table --}}
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('milk.date') }}</th>
                    <th>{{ __('milk.morning') }} (L)</th>
                    <th>{{ __('milk.evening') }} (L)</th>
                    <th>{{ __('milk.total') }} (L)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daily as $row)
                <tr>
                    <td>{{ $row->entry_date }}</td>
                    <td>{{ number_format($row->morning,1) }}</td>
                    <td>{{ number_format($row->evening,1) }}</td>
                    <td style="font-weight:600; color:var(--primary);">
                        {{ number_format($row->total,1) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:30px; color:#9ca3af;">
                        {{ __('milk.no_data') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection