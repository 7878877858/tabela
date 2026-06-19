@extends('layouts.app')
@section('title', __('employee.employees'))

@section('content')

<x-section-header :title="__('employee.employees')" icon="👷" />

<x-form-card :title="__('employee.new_employee')" icon="➕">
    <form method="POST" action="{{ route('employees.store') }}">
        @csrf
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">{{ __('employee.name') }} *</label>
                <input type="text" name="name" class="form-control" placeholder="{{ __('employee.name_placeholder') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('employee.mobile') }}</label>
                <input type="tel" name="mobile" class="form-control" placeholder="{{ __('employee.mobile_placeholder') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('employee.join_date') }} *</label>
                <input type="date" name="join_date" class="form-control" value="{{ today()->toDateString() }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('employee.employee_type') }} *</label>

                <select name="employee_type" class="form-control" required>
                    <option value="employee">{{ __('employee.employee_labour') }}</option>
                    <option value="committee">{{ __('employee.committee_member') }}</option>
                </select>
            </div>
             <div class="grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('employee.monthly_salary') }} (₹) *</label>
                <input type="number" name="monthly_salary" step="100" min="0" class="form-control" placeholder="8000" required>
            </div>
        </div>
        </div>
       
        <button type="submit" class="btn btn-primary">➕ {{ __('employee.add') }}</button>
    </form>
</x-form-card>

{{-- List --}}
<x-erp-listing :paginator="$employees" :per-page="$perPage" :search="true" search-placeholder="નામ / મોબાઇલ શોધો..." id="employees">
@foreach($employees as $emp)
<x-form-card style="margin-bottom:16px;">
    <div class="employee-list-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
        <div>
            <h3 style="font-size:16px; font-weight:700;">
    <span class="text-muted" style="font-size:13px;margin-right:6px;">{{ __('common.sr_no') }} {{ $employees->firstItem() + $loop->index }}</span>
    {{ $emp->name }}

    <span class="badge {{ $emp->status==='active' ? 'badge-green' : 'badge-gray' }}"
          style="margin-left:8px; font-size:11px;">
        {{ $emp->status==='active' ? __('employee.active') : __('employee.inactive') }}
    </span>

    @if($emp->employee_type == 'committee')
        <span class="badge badge-blue" style="margin-left:5px;">
            👥 {{ __('employee.committee_badge') }}
        </span>
    @else
        <span class="badge badge-yellow" style="margin-left:5px;">
            👷 {{ __('employee.labour_worker') }}
        </span>
    @endif
</h3>
            <!-- <h3 style="font-size:16px; font-weight:700;">{{ $emp->name }}
                <span class="badge {{ $emp->status==='active' ? 'badge-green' : 'badge-gray' }}" style="margin-left:8px; font-size:11px;">{{ $emp->status==='active' ? __('employee.active') : __('employee.inactive') }}</span>
            </h3> -->
            <p style="font-size:13px; color:#6b7280;">📞 {{ $emp->mobile ?? '—' }} | {{ __('employee.joined') }}: {{ $emp->join_date->format('d/m/Y') }} | {{ __('employee.salary') }}: ₹{{ number_format($emp->monthly_salary,0) }}/મહ.</p>
        </div>
        <div class="mobile-card-actions">
            @php $pending = $emp->pendingMonths() @endphp
            @if($pending > 0)
            <span class="badge badge-yellow">⏳ {{ $pending }} {{ __('employee.pending_months') }} — ₹{{ number_format($pending * $emp->monthly_salary,0) }}</span>
            @else
            <span class="badge badge-green">✅ {{ __('employee.updated') }}</span>
            @endif
<a href="{{ route('employee.portal',$emp) }}"
                class="btn btn-outline btn-sm">
                📋 {{ __('employee.portal') }}
                </a>
            {{-- Pay salary --}}
            <button onclick="document.getElementById('pay-{{ $emp->id }}').classList.toggle('hidden')" class="btn btn-outline btn-sm">💰 {{ __('employee.pay_salary') }}</button>
        </div>
    </div>

    {{-- Pay form --}}
    <div id="pay-{{ $emp->id }}" class="hidden" style="margin-top:14px; padding-top:14px; border-top:1px solid #f3f4f6;">
        <form method="POST" action="{{ route('employees.salary',$emp) }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
            @csrf
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="margin-bottom:4px;">{{ __('employee.month') }}</label>
                <select name="month" class="form-control" style="width:120px;">
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m==now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('M') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="margin-bottom:4px;">{{ __('employee.year') }}</label>
                <input type="number" name="year" value="{{ now()->year }}" class="form-control" style="width:90px;">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="margin-bottom:4px;">{{ __('employee.amount') }} (₹)</label>
                <input type="number" name="amount" value="{{ $emp->monthly_salary }}" class="form-control" style="width:120px;">
            </div>
            
            <button type="submit" class="btn btn-primary">✅ {{ __('employee.paid') }}</button>
            
        </form>
    </div>
</x-form-card>
@endforeach
</x-erp-listing>

@endsection

@push('scripts')
<script>
document.querySelectorAll('[id^="pay-"]').forEach(el => el.classList.add('hidden'));
</script>
<style>.hidden { display: none !important; }</style>
@endpush