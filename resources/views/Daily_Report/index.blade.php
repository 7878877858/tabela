@extends('layouts.app')

@section('title', 'દૈનિક સ્ટાફ કાર્ય અહેવાલ')

@section('content')

<div class="container-fluid">

    <!-- Header -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">

            <div>
                <h3 class="fw-bold mb-1">
                    📋 દૈનિક સ્ટાફ કાર્ય અહેવાલ
                </h3>
                <small class="text-muted">
                    Daily Dairy Farm Report Management
                </small>
            </div>

            <a href="{{ route('daily-reports.create') }}"
                class="btn btn-success">
                <i class="fa fa-plus"></i>
                નવો અહેવાલ
            </a>

        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h2 class="text-primary fw-bold">
                        {{ $reports->count() }}
                    </h2>
                    <small>કુલ રિપોર્ટ</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h2 class="text-success fw-bold">
                        {{ date('d') }}
                    </h2>
                    <small>આજની તારીખ</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h2 class="text-warning fw-bold">
                        0
                    </h2>
                    <small>કુલ પશુ</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h2 class="text-danger fw-bold">
                        0
                    </h2>
                    <small>કુલ કર્મચારી</small>
                </div>
            </div>
        </div>

    </div>

    <!-- Reports Table -->

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                📑 રિપોર્ટ યાદી
            </h5>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>#</th>
                            <th>તારીખ</th>
                            <th>શિફ્ટ</th>
                            <th>અહેવાલ નંબર</th>
                            <th>બનાવનાર</th>
                            <th width="180">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($reports as $report)

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ date('d-m-Y', strtotime($report->report_date)) }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>DR-{{ $report->id }}</td>
                            <td>Admin</td>

                            <td>
                                <a href="{{ route('daily-reports.show',$report->id) }}"
                                    class="btn btn-sm btn-primary">
                                    View
                                </a>

                                <a href="{{ route('daily-reports.edit',$report->id) }}"
                                    class="btn btn-sm btn-warning">
                                    Edit
                                </a>
                            </td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="6" class="text-center">
                                No Reports Found
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>


            </div>

        </div>

    </div>

</div>

@endsection