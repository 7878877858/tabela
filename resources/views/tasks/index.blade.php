@extends('layouts.app')

@section('title', __('tasks.task_management'))

@section('content')

<div class="page-header">
    <h2>📋 {{ __('tasks.task_management') }}</h2>
</div>

{{-- Statistics --}}
<div class="grid-4" style="margin-bottom:20px;">

    <div class="stat-card">
        <div class="label">📋 {{ __('tasks.total_tasks') }}</div>
        <div class="value">{{ $taskStats['total'] ?? 0 }}</div>
    </div>

    <div class="stat-card">
        <div class="label">⏳ {{ __('tasks.pending') }}</div>
        <div class="value" style="color:#f59e0b;">
           {{ $taskStats['pending'] ?? 0 }}
        </div>
    </div>

    <div class="stat-card">
        <div class="label">🔄 {{ __('tasks.in_progress') }}</div>
        <div class="value" style="color:#3b82f6;">
            {{ $taskStats['in_progress'] ?? 0 }}
        </div>
    </div>

    <div class="stat-card">
        <div class="label">✅ {{ __('tasks.completed') }}</div>
        <div class="value" style="color:#16a34a;">
            {{ $taskStats['completed'] ?? 0 }}
        </div>
    </div>

</div>

{{-- Add Task --}}
<div class="card" style="margin-bottom:20px;">

    <h3 style="font-size:16px;font-weight:700;margin-bottom:18px;">
        📌 {{ __('tasks.assign_new_task') }}
    </h3>

    <form method="POST" action="{{ route('tasks.store') }}">
        @csrf

        <div class="grid-2">

            <div class="form-group">
                <label class="form-label">{{ __('tasks.task_title') }} *</label>
                <input type="text"
                       name="title"
                       class="form-control"
                       placeholder="{{ __('tasks.task_title_placeholder') }}"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('tasks.assign_to') }} *</label>
                <select name="employee_id" class="form-control" required>
                    <option value="">{{ __('tasks.select_employee') }}</option>

                    @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">
                        {{ $employee->name }}
                    </option>
                    @endforeach

                </select>
            </div>

        </div>

        <div class="grid-3">

            <div class="form-group">
                <label class="form-label">{{ __('tasks.priority') }}</label>

                <select name="priority" class="form-control">

                    <option value="low">
                        🟢 {{ __('tasks.low') }}
                    </option>

                    <option value="medium" selected>
                        🟡 {{ __('tasks.medium') }}
                    </option>

                    <option value="high">
                        🔴 {{ __('tasks.high') }}
                    </option>

                </select>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('tasks.recurrence') }}</label>

                <select name="recurrence" class="form-control">

                    <option value="none">
                        {{ __('tasks.no_repeat') }}
                    </option>

                    <option value="daily">
                        {{ __('tasks.daily') }}
                    </option>

                    <option value="monthly">
                        {{ __('tasks.monthly') }}
                    </option>

                </select>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('tasks.status') }}</label>

                <select name="status" class="form-control">
                    <option value="pending">{{ __('tasks.pending') }}</option>
                    <option value="in_progress">{{ __('tasks.in_progress') }}</option>
                </select>
            </div>

        </div>

        <div class="grid-2">

            <div class="form-group">
                <label class="form-label">{{ __('tasks.start_date') }}</label>
                <input type="date"
                       name="start_date"
                       class="form-control"
                       value="{{ date('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('tasks.due_date') }}</label>
                <input type="date"
                       name="due_date"
                       class="form-control">
            </div>

        </div>

        <div class="form-group">
            <label class="form-label">{{ __('tasks.description') }}</label>

            <textarea name="description"
                      rows="3"
                      class="form-control"
                      placeholder="{{ __('tasks.description_placeholder') }}"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            ➕ {{ __('tasks.assign_task') }}
        </button>

    </form>

</div>

{{-- Task List --}}
<div class="card">

    <h3 style="font-size:16px;font-weight:700;margin-bottom:18px;">
        📋 {{ __('tasks.active_tasks') }}
    </h3>

    <x-erp-listing :paginator="$tasks" :per-page="$perPage" :search="true" search-placeholder="કાર્ય / કર્મચારી શોધો..." id="tasks">

    <div class="table-wrap">

        <table>

            <thead>
            <tr>
                <th>{{ __('common.sr_no') }}</th>
                <th>{{ __('tasks.task') }}</th>
                <th>{{ __('tasks.employee') }}</th>
                <th>{{ __('tasks.priority') }}</th>
                <th>{{ __('tasks.repeat') }}</th>
                <th>{{ __('tasks.status') }}</th>
                <th>{{ __('tasks.due_date') }}</th>
                <th width="140">{{ __('tasks.action') }}</th>
            </tr>
            </thead>

            <tbody>

            @forelse($tasks as $task)

            <tr>

                <td>{{ $tasks->firstItem() + $loop->index }}</td>

                <td>
                    <strong>{{ $task->title }}</strong>
                </td>

                <td>
                    {{ $task->employee->name ?? '-' }}
                </td>

                <td>

                    @if($task->priority == 'high')
                        <span class="badge badge-red">🔴 {{ __('tasks.high') }}</span>
                    @elseif($task->priority == 'medium')
                        <span class="badge badge-yellow">🟡 {{ __('tasks.medium') }}</span>
                    @else
                        <span class="badge badge-green">🟢 {{ __('tasks.low') }}</span>
                    @endif

                </td>

                <td>
                    {{ ucfirst($task->recurrence) }}
                </td>

                <td>

                    @if($task->status == 'completed')
                        <span class="badge badge-green">{{ __('tasks.completed') }}</span>
                    @elseif($task->status == 'in_progress')
                        <span class="badge badge-blue">{{ __('tasks.in_progress') }}</span>
                    @else
                        <span class="badge badge-yellow">{{ __('tasks.pending') }}</span>
                    @endif

                </td>

                <td>
                    {{ $task->due_date }}
                </td>

                <td data-label="" class="mobile-card-actions erp-listing__actions">

                    <div class="mobile-card-actions__group">

                        <a href="{{ route('tasks.edit',$task) }}"
                        class="btn btn-outline btn-sm">
                            ✏️
                        </a>

                        <form method="POST"
                            action="{{ route('tasks.complete',$task) }}">
                            @csrf

                            <button type="submit"
                                    class="btn btn-success btn-sm">
                                ✅
                            </button>
                        </form>

                        <form method="POST"
                            action="{{ route('tasks.destroy',$task) }}"
                            onsubmit="return confirm('Delete Task?')">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-danger btn-sm">
                                🗑
                            </button>

                        </form>

                    </div>

                </td>

            </tr>

            @empty

            <tr>
                <td colspan="8"
                    style="text-align:center;padding:30px;color:#9ca3af;">
                    {{ __('tasks.no_tasks_found') }}
                </td>
            </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    </x-erp-listing>

</div>

@endsection