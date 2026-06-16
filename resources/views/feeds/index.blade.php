@extends('layouts.app')

@section('content')
<style>
    .action-column {
        white-space: nowrap;
    }

    .action-column .action-btn {
        display: inline-block;
        margin-right: 6px;
        vertical-align: middle;
    }

    .action-column form {
        display: inline;
    }

    .action-column button {
        border: none;
        cursor: pointer;
    }
    @media (max-width: 768px) {

    .table-responsive {
        overflow-x: auto;
    }

    .table {
        min-width: 700px;
    }

    .action-column {
        white-space: nowrap;
        min-width: 120px;
    }

    .action-column a,
    .action-column button {
        margin-right: 4px;
    }
}
</style>
@extends('layouts.app')
@section('title', 'ફીડ માસ્ટર')

@section('content')
<div class="page-header">
    <h2>🌾 ફીડ માસ્ટર</h2>
    <a href="{{ route('feeds.create') }}" class="btn btn-primary">➕ નવી ફીડ</a>
</div>
<div class="card">
    <div class="card shadow-sm border-0">

       

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>ફીડ નામ</th>
                            <th class="text-center">સ્ટોક</th>
                            <th class="text-center">એકમ</th>
                            <th class="text-center">સ્થિતિ</th>
                            <th class="text-center">ક્રિયા</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($feeds as $feed)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <strong>
                                    {{ $feed->name }}
                                </strong>
                                <br>
                                <small class="text-muted">
                                    {{ $feed->description }}
                                </small>
                            </td>

                            <td class="text-center">
                                <span class="fw-semibold text-primary">
                                    {{ number_format($feed->volume,2) }}
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-info text-dark">
                                    {{ $feed->unit }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if($feed->status)
                                <span class="badge bg-success">
                                    Active
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    Inactive
                                </span>
                                @endif
                            </td>
                            <td class="action-column">
                                <a href="{{ route('feeds.show',$feed->id) }}" class="btn btn-outline btn-sm">👁</a>
                                <a href="{{ route('feeds.edit',$feed->id) }}" class="btn btn-ghost btn-sm">✏️</a>
                                <form method="POST"
                                    action="{{ route('feeds.destroy',$feed->id) }}"
                                    onsubmit="return confirm('{{ __('feeds.delete_confirm') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                                </form>


                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    @endsection