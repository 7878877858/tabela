@extends('layouts.app')

@section('title', __('tasks.edit_task'))

@section('content')

<div class="card">

    <h2>✏️ {{ __('tasks.edit_task') }}</h2>

    <form method="POST"
          action="{{ route('tasks.update',$task) }}">

        @csrf
        @method('PUT')

        <div class="grid-2">

            <div class="form-group">
                <label>{{ __('tasks.employee') }}</label>

                <select name="employee_id"
                        class="form-control">

                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}"
                            {{ $task->employee_id == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="form-group">
                <label>{{ __('tasks.task_title') }}</label>

                <input type="text"
                       name="title"
                       value="{{ $task->title }}"
                       class="form-control">
            </div>

            <div class="form-group">
                <label>{{ __('tasks.priority') }}</label>

                <select name="priority"
                        class="form-control">

                    <option value="low"
                        {{ $task->priority=='low'?'selected':'' }}>
                        {{ __('tasks.low') }}
                    </option>

                    <option value="medium"
                        {{ $task->priority=='medium'?'selected':'' }}>
                        {{ __('tasks.medium') }}
                    </option>

                    <option value="high"
                        {{ $task->priority=='high'?'selected':'' }}>
                        {{ __('tasks.high') }}
                    </option>

                </select>
            </div>

            <div class="form-group">
                <label>{{ __('tasks.status') }}</label>

                <select name="status"
                        class="form-control">

                    <option value="pending"
                        {{ $task->status=='pending'?'selected':'' }}>
                        {{ __('tasks.pending') }}
                    </option>

                    <option value="in_progress"
                        {{ $task->status=='in_progress'?'selected':'' }}>
                        {{ __('tasks.in_progress') }}
                    </option>

                    <option value="completed"
                        {{ $task->status=='completed'?'selected':'' }}>
                        {{ __('tasks.completed') }}
                    </option>

                </select>
            </div>

        </div>

        <div class="form-group">
            <label>{{ __('tasks.description') }}</label>

            <textarea name="description"
                      rows="4"
                      class="form-control">{{ $task->description }}</textarea>
        </div>

        <button type="submit"
                class="btn btn-primary">
            💾 {{ __('tasks.update_task') }}
        </button>

        <a href="{{ route('tasks.index') }}"
           class="btn btn-secondary">
           {{ __('tasks.cancel') }}
        </a>

    </form>

</div>

@endsection