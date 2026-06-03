@extends('layouts.app')

@section('title', $employee->name.' - '.__('employee.portal'))

@section('content')

<div class="page-header">
    <h2>👤 {{ $employee->name }}</h2>
</div>

<div class="grid-3">

    <div class="stat-card">
        <div class="label">{{ __('employee.employee_type') }}</div>
        <div class="value">
            {{ $employee->employee_type == 'committee'
                ? __('employee.committee_member')
                : __('employee.labour_worker') }}
        </div>
    </div>

    <div class="stat-card">
        <div class="label">{{ __('employee.mobile') }}</div>
        <div class="value">{{ $employee->mobile }}</div>
    </div>

    <div class="stat-card">
        <div class="label">{{ __('employee.monthly_salary') }}</div>
        <div class="value">₹{{ number_format($employee->monthly_salary) }}</div>
    </div>

</div>

<div class="card" style="margin-top:20px;">
    <h3>📋 {{ __('employee.today_tasks') }}</h3>

@forelse($tasks as $task)

<div class="card" style="margin-bottom:15px;padding:15px;">

    <div style="display:flex;justify-content:space-between;">
        <h4>{{ $task->title }}</h4>

        <span class="badge">
            {{ ucfirst(str_replace('_',' ',$task->status)) }}
        </span>
    </div>

    <p style="margin-top:10px;">
        {{ $task->description }}
    </p>

    <div style="display:flex;gap:20px;margin-top:10px;flex-wrap:wrap;">

        <div>
            <b>{{ __('employee.priority') }}:</b>
            {{ ucfirst($task->priority) }}
        </div>

        <div>
            <b>{{ __('employee.start') }}:</b>
            {{ $task->start_date }}
        </div>

        <div>
            <b>{{ __('employee.due') }}:</b>
            {{ $task->due_date }}
        </div>

        <div>
            <b>{{ __('employee.repeat') }}:</b>
            {{ ucfirst($task->recurrence) }}
        </div>

    </div>

    @if($task->status != 'completed')

    <form method="POST"
          action="{{ route('tasks.complete',$task) }}"
          style="margin-top:15px;">

        @csrf

        <button type="submit"
                class="btn btn-success">
            ✅ {{ __('employee.mark_completed') }}
        </button>

    </form>

    @endif

</div>

@empty

<div class="card">
    {{ __('employee.no_tasks') }}
</div>

@endforelse
</div>

<div class="card" style="margin-top:20px;">
    <h3>📝 {{ __('employee.daily_report') }}</h3>

    <form>
        <div class="form-group">
            <label>{{ __('employee.work_done') }}</label>
            <textarea class="form-control" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label>{{ __('employee.problems') }}</label>
            <textarea class="form-control" rows="3"></textarea>
        </div>

        <button class="btn btn-primary">
            {{ __('employee.submit_report') }}
        </button>
    </form>
</div>

@endsection