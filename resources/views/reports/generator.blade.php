@extends('layouts.app')
@section('title', 'Report Generator')

@section('content')
<div class="page-header">
    <h2>📊 Dynamic Report Generator</h2>
    <p class="text-muted mb-0" style="font-size:13px;">Reports read from synchronized tables (milk_entries, feed_entries, expenses, incomes, health_records, vaccination_records)</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.generate') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Animal Type</label>
                <select name="animal_type" class="form-select">
                    <option value="">All Animals</option>
                    @foreach($animalTypes as $key => $label)
                    <option value="{{ $key }}" {{ request('animal_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Report Type</label>
                <select name="report_type" class="form-select" required>
                    @foreach(['milk'=>'Milk Report','feed'=>'Feed Report','expense'=>'Expense Report','income'=>'Income Report','health'=>'Health Report','vaccination'=>'Vaccination Report','combined'=>'Combined Report','monthly'=>'Monthly Report','yearly'=>'Yearly Report'] as $val => $label)
                    <option value="{{ $val }}" {{ request('report_type','milk') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">Generate Report</button>
                <button type="submit" formaction="{{ route('reports.pdf') }}" class="btn btn-outline-secondary" formtarget="_blank">Export PDF / Print</button>
            </div>
        </form>
    </div>
</div>
@endsection
