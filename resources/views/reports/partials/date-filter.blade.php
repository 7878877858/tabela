<form method="GET" class="erp-panel" style="margin-bottom:16px; display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
    <div class="form-group mb-0">
        <label class="form-label">{{ __('farm.date_from') }}</label>
        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
    </div>
    <div class="form-group mb-0">
        <label class="form-label">{{ __('farm.date_to') }}</label>
        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">{{ __('farm.filter') }}</button>
</form>
@if(isset($total))
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp
<div class="alert alert-info">{{ __('farm.total') }}: <strong>{{ $currency }}{{ number_format($total, 2) }}</strong></div>
@endif
