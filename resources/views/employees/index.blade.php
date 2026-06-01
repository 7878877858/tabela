@extends('layouts.app')
@section('title','કર્મચારીઓ')

@section('content')
<div class="page-header"><h2>👷 કર્મચારીઓ</h2></div>

{{-- Add employee --}}
<div class="card" style="margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">નવો કર્મચારી</h3>
    <form method="POST" action="{{ route('employees.store') }}">
        @csrf
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">નામ *</label>
                <input type="text" name="name" class="form-control" placeholder="રામ ભાઈ" required>
            </div>
            <div class="form-group">
                <label class="form-label">મોબાઈલ</label>
                <input type="tel" name="mobile" class="form-control" placeholder="9876543210">
            </div>
            <div class="form-group">
                <label class="form-label">જોડાવાની તારીખ *</label>
                <input type="date" name="join_date" class="form-control" value="{{ today()->toDateString() }}" required>
            </div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">માસિક પગાર (₹) *</label>
                <input type="number" name="monthly_salary" step="100" min="0" class="form-control" placeholder="8000" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">➕ ઉમેરો</button>
    </form>
</div>

{{-- List --}}
@foreach($employees as $emp)
<div class="card" style="margin-bottom:16px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
        <div>
            <h3 style="font-size:16px; font-weight:700;">{{ $emp->name }}
                <span class="badge {{ $emp->status==='active' ? 'badge-green' : 'badge-gray' }}" style="margin-left:8px; font-size:11px;">{{ $emp->status==='active' ? 'સક્રિય' : 'નિષ્ક્રિય' }}</span>
            </h3>
            <p style="font-size:13px; color:#6b7280;">📞 {{ $emp->mobile ?? '—' }} | જોડ્યા: {{ $emp->join_date->format('d/m/Y') }} | પગાર: ₹{{ number_format($emp->monthly_salary,0) }}/મહ.</p>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            @php $pending = $emp->pendingMonths() @endphp
            @if($pending > 0)
            <span class="badge badge-yellow">⏳ {{ $pending }} મ. બાકી — ₹{{ number_format($pending * $emp->monthly_salary,0) }}</span>
            @else
            <span class="badge badge-green">✅ અદ્યતન</span>
            @endif

            {{-- Pay salary --}}
            <button onclick="document.getElementById('pay-{{ $emp->id }}').classList.toggle('hidden')" class="btn btn-outline btn-sm">💰 પગાર</button>
        </div>
    </div>

    {{-- Pay form --}}
    <div id="pay-{{ $emp->id }}" class="hidden" style="margin-top:14px; padding-top:14px; border-top:1px solid #f3f4f6;">
        <form method="POST" action="{{ route('employees.salary',$emp) }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
            @csrf
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="margin-bottom:4px;">મહિનો</label>
                <select name="month" class="form-control" style="width:120px;">
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m==now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('M') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="margin-bottom:4px;">વર્ષ</label>
                <input type="number" name="year" value="{{ now()->year }}" class="form-control" style="width:90px;">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="margin-bottom:4px;">રકમ (₹)</label>
                <input type="number" name="amount" value="{{ $emp->monthly_salary }}" class="form-control" style="width:120px;">
            </div>
            <button type="submit" class="btn btn-primary">✅ ચૂકવ્યો</button>
        </form>
    </div>
</div>
@endforeach

<div style="margin-top:16px;">{{ $employees->links() }}</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[id^="pay-"]').forEach(el => el.classList.add('hidden'));
</script>
<style>.hidden { display: none !important; }</style>
@endpush