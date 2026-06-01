@extends('layouts.app')
@section('title','દૂધ ઇતિહાસ')

@section('content')

<div class="page-header">
    <h2>📋 દૂધ ઇતિહાસ</h2>
    <a href="{{ route('milk.index') }}" class="btn btn-outline btn-sm">➕ એન્ટ્રી</a>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px; padding:14px 20px;">
    <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
        
        <label>મહિનો:</label>
        <select name="month" onchange="this.form.submit()" class="form-control" style="width:120px;">
            @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                    {{ $m }}
                </option>
            @endfor
        </select>

        <label>વર્ષ:</label>
        <input type="number" name="year" value="{{ $year }}" 
            class="form-control" style="width:100px;" onchange="this.form.submit()">

        <span style="margin-left:auto; font-size:14px;">
            કુલ દૂધ: 
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
                    <th>તારીખ</th>
                    <th>સવાર (L)</th>
                    <th>સાંજ (L)</th>
                    <th>કુલ (L)</th>
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
                        કોઈ ડેટા નથી
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection