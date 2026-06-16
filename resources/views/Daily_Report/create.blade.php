@extends('layouts.app')
@section('title', 'દૈનિક સ્ટાફ કાર્ય અહેવાલ')

@section('content')
<style>
    .icon {
        font-size: 24px;
        width: 24px;
        text-align: center;
    }

    .dashboard-wrapper {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dashboard-card {
        flex: 1;
        min-width: 170px;
        background: #fff;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #dcdcdc;
    }

    .card-top {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        padding: 10px 8px;

        font-size: 14px;
        font-weight: 600;
    }

    .card-bottom {
        border-top: 1px solid #ddd;

        text-align: center;

        font-size: 22px;
        font-weight: 700;

        padding: 8px;
    }

    .blue {
        border-color: #5c86d6;
    }

    .blue .card-top {
        color: #0d3d8b;
        background: #f5f9ff;
    }

    .blue .card-bottom {
        color: #0d3d8b;
    }

    .green {
        border-color: #59b36c;
    }

    .green .card-top {
        color: #2e7d32;
        background: #f7fff7;
    }

    .green .card-bottom {
        color: #2e7d32;
    }

    .orange {
        border-color: #f0a14c;
    }

    .orange .card-top {
        color: #b85c00;
        background: #fffaf4;
    }

    .orange .card-bottom {
        color: #b85c00;
    }

    .red {
        border-color: #ef8c8c;
    }

    .red .card-top {
        color: #c62828;
        background: #fff8f8;
    }

    .red .card-bottom {
        color: #c62828;
    }

    .purple {
        border-color: #a97ae3;
    }

    .purple .card-top {
        color: #5e35b1;
        background: #fbf8ff;
    }

    .purple .card-bottom {
        color: #5e35b1;
    }

    @media(max-width:768px) {

        .dashboard-wrapper {
            display: block;
        }

        .dashboard-card {
            margin-bottom: 10px;
        }
    }



    /* Mobile */

    .summary-row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }


    /* Mobile */

    @media(max-width:768px) {

        .summary-row {
            display: block;
        }

        .summary-box {
            margin-bottom: 10px;
        }
    }

    /* Mobile Responsive */

    @media (max-width: 768px) {

        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-size: 18px !important;
        }

        .card-header {
            font-size: 14px;
            text-align: center;
            padding: 10px;
        }

        .btn {
            width: 100%;
            margin-top: 10px;
        }

        .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        /* ALL TABLES */
        table,
        thead,
        tbody,
        th,
        td,
        tr {
            display: block;
            width: 100%;
        }

        table thead {
            display: none;
        }

        table tr {
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 15px;
            background: #fff;
            padding: 10px;
        }

        table td::before {
            content: attr(data-label);
            display: block;
            font-weight: 700;
            color: #000000;
            /* Red */
            margin-bottom: 5px;
            font-size: 13px;
        }

        table td {
            content: attr(data-label);
            border: none !important;
            padding: 5px 0 !important;
        }

        table td .form-control,
        table td select {
            width: 100% !important;
            min-width: 100% !important;
        }

        /* Summary Cards */

        .summary-card,
        .card.border-primary,
        .card.border-success,
        .card.border-warning,
        .card.border-danger,
        .card.border-info {
            margin-bottom: 15px;
        }

        .fw-bold {
            font-size: 24px !important;
        }

        /* Date Inputs */

        input[type="date"] {
            width: 100%;
        }

        /* Select */

        select.form-control {
            width: 100%;
        }

        /* Textarea */

        textarea {
            width: 100%;
        }



    }
</style>
<form action="{{ isset($report)
        ? route('daily-reports.update',$report->id)
        : route('daily-reports.store') }}"
    method="POST">

    @csrf

    @if(isset($report))
    @method('PUT')
    @endif
    @csrf
    <div class="container-fluid">


        <div class="card border-0 shadow-lg mb-4">
            <div class="card-body bg-primary text-white">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <h3 class="mb-1">
                            📋 દૈનિક સ્ટાફ કાર્ય અહેવાલ
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Summary -->
        <br>
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-dark text-white">
                📊 આજનો સારાંશ
            </div>

            <div class="card-body">

                <div class="d-flex flex-nowrap overflow-auto gap-2 pb-2">

                    <div class="summary-row">

                        <div class="dashboard-card blue">

                            <div class="card-top">
                                <span class="icon">🐄</span>
                                <span class="title">કુલ પશુ</span>
                            </div>

                            <div class="card-bottom">
                                {{ isset($totalAnimals) ? $totalAnimals : 0 }}
                            </div>

                        </div>
                        <!-- દૂધ આપતા -->
                        <div class="dashboard-card green">
                            <div class="card-top">
                                <span class="icon">🥛</span>
                                <span class="title">દૂધ આપતા</span>
                            </div>
                            <div class="card-bottom">
                                {{ isset($lactatingAnimals) ? $lactatingAnimals : 0 }}
                            </div>
                        </div>

                        <!-- ગર્ભવતી -->
                        <div class="dashboard-card orange">
                            <div class="card-top">
                                <span class="icon">🤰</span>
                                <span class="title">ગર્ભવતી</span>
                            </div>
                            <div class="card-bottom">
                                {{ isset($pregnantAnimals) ? $pregnantAnimals : 0 }}
                            </div>
                        </div>

                        <!-- હીટ માં પશુ -->
                        <div class="dashboard-card red">
                            <div class="card-top">
                                <span class="icon">❤️</span>
                                <span class="title">હીટ માં પશુ</span>
                            </div>
                            <div class="card-bottom">
                                {{ isset($heatAnimals) ? $heatAnimals : 0 }}
                            </div>
                        </div>

                        <!-- કુલ દૂધ -->
                        <div class="dashboard-card purple">
                            <div class="card-top">
                                <span class="icon">🥛</span>
                                <span class="title">કુલ દૂધ</span>
                            </div>
                            <div class="card-bottom">
                                {{ number_format($totalMilk ?? 0, 2) }}
                            </div>
                        </div>



                    </div>

                </div>

            </div>

        </div>
        <!-- Basic Info -->
        <br>
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-info text-white">
                🏡 મૂળભૂત માહિતી
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label data-label="તારીખ">તારીખ</label>
                        <input type="date"
                            name="report_date"
                            class="form-control"
                            value="{{ old('report_date', $report->report_date ?? date('Y-m-d')) }}"
                            required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label data-label="શિફ્ટ">શિફ્ટ</label>

                        <select name="shift" class="form-control" required>

                            <option value="">શિફ્ટ પસંદ કરો</option>

                            <option value="સવાર"
                                {{ old('shift', $report->shift ?? '') == 'સવાર' ? 'selected' : '' }}>
                                સવાર
                            </option>

                            <option value="સાંજ"
                                {{ old('shift', $report->shift ?? '') == 'સાંજ' ? 'selected' : '' }}>
                                સાંજ
                            </option>

                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label data-label="અહેવાલ નંબર">અહેવાલ નંબર</label>


                        <input type="text"
                            name="report_number"
                            class="form-control"
                            value="{{ old('report_number', $report->report_number ?? $reportNumber) }}"
                            readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label data-label="રિપોર્ટ બનાવનાર">રિપોર્ટ બનાવનાર</label>

                        <!-- <input type="text"
                            name="reporter"
                            class="form-control"
                            placeholder="Enter Reporter Name"
                            value="{{ old('reporter', $report->reporter ?? '') }}"> -->

                        <select name="reporter" class="form-control">

                            <option value="">રિપોર્ટ બનાવનાર પસંદ કરો</option>

                            @foreach($committeeMembers as $member)

                            <option value="{{ $member->name }}"
                                {{ old('reporter', $report->reporter ?? '') == $member->name ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>

                            @endforeach

                        </select>
                    </div>

                </div>

            </div>

        </div>

        <!-- Staff Attendance -->
        <br>
        <div class="card shadow-sm border-0 mb-4">


            <div class="card-header bg-danger text-white"
                style="display:flex;justify-content:space-between;align-items:center;">

                <span>👨‍🌾 સ્ટાફ હાજરી</span>

                <div>
                    <i class="fa-solid fa-circle-plus text-success"
                        id="addStaffRow"
                        style="color:#198754;font-size:22px;cursor:pointer;"></i>


                </div>

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th data-label="કર્મચારી">કર્મચારી</th>
                            <th data-label="હાજરી">હાજરી</th>
                            <th data-label="નોંધ">નોંધ</th>
                        </tr>
                    </thead>

                    <tbody id="staffBody">

                        @if(isset($report) && $report->staff->count())

                        @foreach($report->staff as $index => $staff)

                        <tr>

                            <td data-label="કર્મચારી">
                                <select name="employee_id[]" class="form-control" required>

                                    <option value="">કર્મચારી પસંદ કરો</option>

                                    @foreach($employees as $employee)

                                    <option value="{{ $employee->id }}"
                                        {{ $employee->id == $staff->employee_id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>

                                    @endforeach

                                </select>
                            </td>

                            <td data-label="હાજરી">
                                <select name="status[]" class="form-control" required>

                                    <option value="present"
                                        {{ $staff->status == 'present' ? 'selected' : '' }}>
                                        Present
                                    </option>

                                    <option value="absent"
                                        {{ $staff->status == 'absent' ? 'selected' : '' }}>
                                        Absent
                                    </option>

                                    <option value="leave"
                                        {{ $staff->status == 'leave' ? 'selected' : '' }}>
                                        Leave
                                    </option>

                                </select>
                            </td>

                            <td data-label="નોંધ">
                                <input type="text"
                                    name="remarks[]"
                                    class="form-control"
                                    value="{{ $staff->remarks }}"
                                    placeholder="Enter Remarks">
                            </td>

                            <td data-label="Action" class="text-center">
                                <i class="fa-solid fa-circle-minus text-danger remove-staff-row"
                                    style="font-size:22px;cursor:pointer;{{ $index == 0 ? 'display:none;' : '' }}"></i>
                            </td>

                        </tr>

                        @endforeach

                        @else

                        {{-- Create Page Row --}}

                        <tr>

                            <td data-label="કર્મચારી">
                                <select name="employee_id[]" class="form-control" required>

                                    <option value="">કર્મચારી પસંદ કરો</option>

                                    @foreach($employees as $employee)

                                    <option value="{{ $employee->id }}">
                                        {{ $employee->name }}
                                    </option>

                                    @endforeach

                                </select>
                            </td>

                            <td data-label="હાજરી">
                                <select name="status[]" class="form-control" required>
                                    <option value="">હાજરી પસંદ કરો</option>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="leave">Leave</option>
                                </select>
                            </td>

                            <td data-label="નોંધ">
                                <input type="text"
                                    name="remarks[]"
                                    class="form-control"
                                    placeholder="Enter Remarks">
                            </td>

                            <td data-label="Action" class="text-center">
                                <i class="fa-solid fa-circle-minus text-danger remove-staff-row"
                                    style="display:none;font-size:22px;cursor:pointer;"></i>
                            </td>

                        </tr>

                        @endif

                    </tbody>

                </table>

            </div>

        </div>

        <!-- Milk Production -->
        <br>
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-danger text-white"
                style="display:flex;justify-content:space-between;align-items:center;">

                <span>🥛 દૂધ ઉત્પાદન</span>

                <div>
                    <i class="fa-solid fa-circle-plus text-success"
                        id="addMilkRow"
                        style="color:#198754;font-size:22px;cursor:pointer;"></i>


                </div>

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <thead>

                        <tr>
                            <th data-label="પશુ નં">પશુ નં</th>
                            <th data-label="પશુ નામ">પશુ નામ</th>
                            <th data-label="સવારનું દૂધ">સવારનું દૂધ</th>
                            <th data-label="સાંજનું દૂધ">સાંજનુ દૂધ</th>
                            <th data-label="કુલ">કુલ</th>
                        </tr>
                    <tbody id="milkBody">

                        @if(isset($report) && $report->milk->count())

                        @foreach($report->milk as $index => $milk)

                        <tr>

                            <td data-label="પશુ નં">
                                <select name="buffalo_id[]" class="form-control buffalo-select">

                                    <option value="">પશુ પસંદ કરો</option>

                                    @foreach($buffaloes as $buffalo)

                                    <option value="{{ $buffalo->id }}"
                                        data-name="{{ $buffalo->name }}"
                                        {{ $buffalo->id == $milk->buffalo_id ? 'selected' : '' }}>

                                        {{ $buffalo->tag_number }}

                                    </option>

                                    @endforeach

                                </select>
                            </td>

                            <td data-label="પશુ નામ">
                                @php
                                $selectedBuffalo = $buffaloes->where('id',$milk->buffalo_id)->first();
                                @endphp

                                <input type="text"
                                    class="form-control buffalo-name"
                                    value="{{ $selectedBuffalo->name ?? '' }}"
                                    readonly>
                            </td>

                            <td>
                                <input type="number"
                                    step="0.01"
                                    name="morning_milk[]"
                                    class="form-control morning"
                                    value="{{ $milk->morning_milk }}"
                                    placeholder="સવારનું દૂધ">
                            </td>

                            <td>
                                <input type="number"
                                    step="0.01"
                                    name="evening_milk[]"
                                    class="form-control evening"
                                    value="{{ $milk->evening_milk }}"
                                    placeholder="સાંજનું દૂધ">
                            </td>

                            <td>
                                <input type="number"
                                    class="form-control total"
                                    value="{{ $milk->total_milk }}"
                                    readonly>
                            </td>

                            <td class="text-center">
                                <i class="fa-solid fa-circle-minus text-danger remove-milk-row"
                                    style="font-size:22px;cursor:pointer;{{ $index==0 ? 'display:none;' : '' }}"></i>
                            </td>

                        </tr>

                        @endforeach

                        @else

                        {{-- Create Page Default Row --}}

                        <tr>
                            <td>
                                <select name="buffalo_id[]" class="form-control buffalo-select">
                                    <option value="">પશુ પસંદ કરો</option>
                                    @foreach($buffaloes as $buffalo)
                                    <option value="{{ $buffalo->id }}"
                                        data-name="{{ $buffalo->name }}">
                                        {{ $buffalo->tag_number }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="text"
                                    class="form-control buffalo-name"
                                    readonly>
                            </td>

                            <td>
                                <input type="number"
                                    step="0.01"
                                    name="morning_milk[]"
                                    class="form-control morning">
                            </td>

                            <td>
                                <input type="number"
                                    step="0.01"
                                    name="evening_milk[]"
                                    class="form-control evening">
                            </td>

                            <td>
                                <input type="number"
                                    class="form-control total"
                                    readonly>
                            </td>

                            <td class="text-center">
                                <i class="fa-solid fa-circle-minus text-danger remove-milk-row"
                                    style="display:none;font-size:22px;cursor:pointer;"></i>
                            </td>
                        </tr>

                        @endif

                    </tbody>
                    </thead>

                </table>

            </div>

        </div>

        <!-- Feed Section -->
        <br>
        <div class="card shadow-sm border-0 mb-4">


            <div class="card-header bg-danger text-white"
                style="display:flex;justify-content:space-between;align-items:center;">

                <span>🌾 ચારો અને ખોરાક</span>

                <div>
                    <i class="fa-solid fa-circle-plus text-success"
                        id="addFeedRow"
                        style="color:#198754;font-size:22px;cursor:pointer;"></i>


                </div>

            </div>

            <div class="card-body">



                <table class="table table-bordered" id="feedTable">

                    <thead>

                        <tr>
                            <th data-label="પશુ નં">પશુ નં</th>
                            <th data-label="સવારનો ખોરાક">સવારનો ખોરાક</th>
                            <th data-label="જથ્થો">જથ્થો</th>
                            <th data-label="સાંજનો ખોરાક">સાંજનો ખોરાક</th>
                            <th data-label="જથ્થો">જથ્થો</th>
                        </tr>
                    <tbody id="feedBody">

                        @php
                        $feedGroups = isset($report)
                        ? $report->feed->groupBy('buffalo_id')
                        : collect();
                        @endphp

                        @if(isset($report) && $feedGroups->count())

                        @foreach($feedGroups as $buffaloId => $rows)

                        @php
                        $morning = $rows->where('feed_time', 'morning')->first();
                        $evening = $rows->where('feed_time', 'evening')->first();
                        @endphp

                        <tr>

                            {{-- Buffalo --}}

                            <td>

                                <select name="feed_buffalo_id[]"
                                    class="form-control">

                                    <option value="">પશુ પસંદ કરો</option>

                                    @foreach($buffaloes as $buffalo)

                                    <option value="{{ $buffalo->id }}"
                                        {{ $buffalo->id == $buffaloId ? 'selected' : '' }}>

                                        {{ $buffalo->tag_number }}

                                    </option>

                                    @endforeach

                                </select>

                            </td>

                            {{-- Morning Feed --}}

                            <td>

                                <select name="morning_feed_type[]"
                                    class="form-control">

                                    <option value="">
                                        ચારો પસંદ કરો
                                    </option>

                                    @foreach($feeds as $feed)

                                    <option value="{{ $feed->name }}"
                                        {{ ($morning && $feed->name == $morning->feed_name) ? 'selected' : '' }}>

                                        {{ $feed->name }}

                                    </option>

                                    @endforeach

                                </select>

                            </td>

                            {{-- Morning Qty --}}

                            <td>

                                <input type="number"
                                    step="0.01"
                                    name="morning_qty[]"
                                    class="form-control"
                                    value="{{ $morning->quantity ?? '' }}">

                            </td>

                            {{-- Evening Feed --}}

                            <td>

                                <select name="evening_feed_type[]"
                                    class="form-control">

                                    <option value="">
                                        ચારો પસંદ કરો
                                    </option>

                                    @foreach($feeds as $feed)

                                    <option value="{{ $feed->name }}"
                                        {{ ($evening && $feed->id == $evening->feed_name) ? 'selected' : '' }}>

                                        {{ $feed->name }}

                                    </option>

                                    @endforeach

                                </select>

                            </td>

                            {{-- Evening Qty --}}

                            <td>

                                <input type="number"
                                    step="0.01"
                                    name="evening_qty[]"
                                    class="form-control"
                                    value="{{ $evening->quantity ?? '' }}">

                            </td>

                            <td class="text-center">

                                <i class="fa-solid fa-circle-minus text-danger remove-feed-row"
                                    style="font-size:22px;cursor:pointer;{{ $loop->first ? 'display:none;' : '' }}"></i>

                            </td>

                        </tr>

                        @endforeach

                        @else

                        {{-- Create Page Default Row --}}

                        <tr>

                            <td>

                                <select name="feed_buffalo_id[]"
                                    class="form-control">

                                    <option value="">પશુ પસંદ કરો</option>

                                    @foreach($buffaloes as $buffalo)

                                    <option value="{{ $buffalo->id }}">
                                        {{ $buffalo->tag_number }}
                                    </option>

                                    @endforeach

                                </select>

                            </td>

                            <td>

                                <select name="morning_feed_type[]"
                                    class="form-control">

                                    <option value="">ચારો પસંદ કરો</option>

                                    @foreach($feeds as $feed)

                                    <option value="{{ $feed->id }}">
                                        {{ $feed->name }}
                                    </option>

                                    @endforeach

                                </select>

                            </td>

                            <td>

                                <input type="number"
                                    step="0.01"
                                    name="morning_qty[]"
                                    class="form-control">

                            </td>

                            <td>

                                <select name="evening_feed_type[]"
                                    class="form-control">

                                    <option value="">ચારો પસંદ કરો</option>

                                    @foreach($feeds as $feed)

                                    <option value="{{ $feed->id }}">
                                        {{ $feed->name }}
                                    </option>

                                    @endforeach

                                </select>

                            </td>

                            <td>

                                <input type="number"
                                    step="0.01"
                                    name="evening_qty[]"
                                    class="form-control">

                            </td>

                            <td class="text-center">

                                <i class="fa-solid fa-circle-minus text-danger remove-feed-row"
                                    style="display:none;font-size:22px;cursor:pointer;"></i>

                            </td>

                        </tr>

                        @endif

                    </tbody>
                    </thead>

                </table>

            </div>

        </div>

        <!-- Health Section -->
        <br>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-danger text-white health-header">

                <div class="card-header bg-danger text-white"
                    style="display:flex;justify-content:space-between;align-items:center;padding:15px 20px;">

                    <span style="font-size:18px;font-weight:600;">
                        🏥 આરોગ્ય અને સારવાર
                    </span>

                    <div style="display:flex;align-items:center;gap:15px;">

                        <i class="fa-solid fa-chevron-down"
                            id="healthToggleIcon"
                            style="cursor:pointer;font-size:18px;"></i>

                        <i class="fa-solid fa-circle-plus text-success"
                            id="addHealthRow"
                            style="color:#198754;font-size:22px;cursor:pointer;"></i>

                    </div>

                </div>
                <div id="healthContent" style="display:none;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>પશુ</th>
                                <th>સમસ્યા</th>
                                <th>સારવાર</th>
                                <th>દવા ખર્ચ</th>
                            </tr>
                        </thead>
                        <tbody id="healthBody">

                            @if(isset($report) && $report->health->count())

                            @foreach($report->health as $index => $health)

                            <tr>

                                <td>
                                    <select name="health_buffalo_id[]" class="form-control">

                                        <option value="">પશુ પસંદ કરો</option>

                                        @foreach($buffaloes as $buffalo)

                                        <option value="{{ $buffalo->id }}"
                                            {{ $buffalo->id == $health->buffalo_id ? 'selected' : '' }}>

                                            {{ $buffalo->tag_number }}

                                        </option>

                                        @endforeach

                                    </select>
                                </td>

                                <td>
                                    <input type="text"
                                        name="health_issue[]"
                                        class="form-control"
                                        value="{{ $health->health_issue }}"
                                        placeholder="Enter Health Issue">
                                </td>

                                <td>
                                    <input type="text"
                                        name="treatment[]"
                                        class="form-control"
                                        value="{{ $health->treatment }}"
                                        placeholder="Enter Treatment Details">
                                </td>

                                <td>
                                    <input type="number"
                                        step="0.01"
                                        name="medicine_cost[]"
                                        class="form-control"
                                        value="{{ $health->medicine_cost }}"
                                        placeholder="Enter Medicine Cost">
                                </td>

                                <td class="text-center">
                                    <i class="fa-solid fa-circle-minus text-danger remove-health-row"
                                        style="font-size:22px;cursor:pointer;{{ $index == 0 ? 'display:none;' : '' }}"></i>
                                </td>

                            </tr>

                            @endforeach

                            @else

                            {{-- Create Page Default Row --}}

                            <tr>

                                <td>
                                    <select name="health_buffalo_id[]" class="form-control">

                                        <option value="">પશુ પસંદ કરો</option>

                                        @foreach($buffaloes as $buffalo)

                                        <option value="{{ $buffalo->id }}">
                                            {{ $buffalo->tag_number }}
                                        </option>

                                        @endforeach

                                    </select>
                                </td>

                                <td>
                                    <input type="text"
                                        name="health_issue[]"
                                        class="form-control"
                                        placeholder="Enter Health Issue">
                                </td>

                                <td>
                                    <input type="text"
                                        name="treatment[]"
                                        class="form-control"
                                        placeholder="Enter Treatment Details">
                                </td>

                                <td>
                                    <input type="number"
                                        step="0.01"
                                        name="medicine_cost[]"
                                        class="form-control"
                                        placeholder="Enter Medicine Cost">
                                </td>

                                <td class="text-center">
                                    <i class="fa-solid fa-circle-minus text-danger remove-health-row"
                                        style="display:none;font-size:22px;cursor:pointer;"></i>
                                </td>

                            </tr>

                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Vaccination Section -->
        <br>
        <div class="card">

            <div class="card-header bg-danger text-white health-header">

                <div class="card-header bg-danger text-white"
                    style="display:flex;justify-content:space-between;align-items:center;padding:15px 20px;">

                    <span style="font-size:18px;font-weight:600;">
                        💉 રસીકરણ
                    </span>

                    <div style="display:flex;align-items:center;gap:15px;">

                        <i class="fa-solid fa-chevron-down"
                            id="vaccinationToggleIcon"
                            style="cursor:pointer;font-size:18px;"></i>

                        <i class="fa-solid fa-circle-plus text-success"
                            id="addVaccinationRow"
                            style="color:#198754;font-size:22px;cursor:pointer;"></i>

                    </div>

                </div>
                <div id="vaccinationContent" style="display:none;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>પશુ</th>
                                <th>રસી</th>
                                <th>તારીખ</th>
                                <th>નોંધ</th>
                            </tr>
                        </thead>

                        <tbody id="vaccinationBody">

                            @if(isset($report) && $report->vaccinations->count())

                            @foreach($report->vaccinations as $index => $vaccination)

                            <tr>

                                <td>
                                    <select name="vaccination_buffalo_id[]" class="form-control">

                                        <option value="">પશુ પસંદ કરો</option>

                                        @foreach($buffaloes as $buffalo)

                                        <option value="{{ $buffalo->id }}"
                                            {{ $buffalo->id == $vaccination->buffalo_id ? 'selected' : '' }}>

                                            {{ $buffalo->tag_number }}

                                        </option>

                                        @endforeach

                                    </select>
                                </td>

                                <td>
                                    <input type="text"
                                        name="vaccine_name[]"
                                        class="form-control"
                                        value="{{ $vaccination->vaccine_name }}"
                                        placeholder="Enter Vaccine Name">
                                </td>

                                <td>
                                    <input type="date"
                                        name="vaccination_date[]"
                                        class="form-control"
                                        value="{{ $vaccination->vaccination_date }}">
                                </td>

                                <td>
                                    <input type="text"
                                        name="vaccination_remarks[]"
                                        class="form-control"
                                        value="{{ $vaccination->remarks }}"
                                        placeholder="Enter Vaccination Remarks">
                                </td>

                                <td class="text-center">
                                    <i class="fa-solid fa-circle-minus text-danger remove-vaccination-row"
                                        style="font-size:22px;cursor:pointer;{{ $index == 0 ? 'display:none;' : '' }}"></i>
                                </td>

                            </tr>

                            @endforeach

                            @else

                            {{-- Create Page Default Row --}}

                            <tr>

                                <td>
                                    <select name="vaccination_buffalo_id[]" class="form-control">

                                        <option value="">પશુ પસંદ કરો</option>

                                        @foreach($buffaloes as $buffalo)

                                        <option value="{{ $buffalo->id }}">
                                            {{ $buffalo->tag_number }}
                                        </option>

                                        @endforeach

                                    </select>
                                </td>

                                <td>
                                    <input type="text"
                                        name="vaccine_name[]"
                                        class="form-control"
                                        placeholder="Enter Vaccine Name">
                                </td>

                                <td>
                                    <input type="date"
                                        name="vaccination_date[]"
                                        class="form-control">
                                </td>

                                <td>
                                    <input type="text"
                                        name="vaccination_remarks[]"
                                        class="form-control"
                                        placeholder="Enter Vaccination Remarks">
                                </td>

                                <td class="text-center">
                                    <i class="fa-solid fa-circle-minus text-danger remove-vaccination-row"
                                        style="display:none;font-size:22px;cursor:pointer;"></i>
                                </td>

                            </tr>

                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pregnancy -->
        <br>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white health-header">
                <div class="card-header bg-danger text-white"
                    style="display:flex;justify-content:space-between;align-items:center;padding:15px 20px;">

                    <span style="font-size:18px;font-weight:600;">
                        🤰 પ્રજનન અને પ્રેગ્નન્સી
                    </span>

                    <div style="display:flex;align-items:center;gap:15px;">

                        <i class="fa-solid fa-chevron-down"
                            id="pregnancyToggleIcon"
                            style="cursor:pointer;font-size:18px;"></i>

                        <i class="fa-solid fa-circle-plus text-success"
                            id="addPregnancyRow"
                            style="color:#198754;font-size:22px;cursor:pointer;"></i>

                    </div>

                </div>
                <div class="card-body p-2">

                    <div class="table-responsive">
                        <div id="pregnancyContent" style="display:none;">
                            <table class="table pregnancy-table" id="pregnancyTable">
                                <thead>
                                    <tr>
                                        <th data-label="પશુ નં">પશુ નં</th>
                                        <th data-label="પશુ નામ">પશુ નામ</th>
                                        <th data-label="છેલ્લી હીટ">છેલ્લી હીટ તારીખ</th>
                                        <th data-label="AI તારીખ">AI તારીખ</th>
                                        <th data-label="પ્રેગ્નન્ટ તારીખ">પ્રેગ્નન્ટ તારીખ</th>
                                        <th data-label="Expected Delivery">અપેક્ષિત પ્રસૂતિ</th>
                                    </tr>
                                <tbody id="pregnancyBody">

                                    @if(isset($report) && $report->pregnancy->count())

                                    @foreach($report->pregnancy as $index => $pregnancy)

                                    @php
                                    $selectedBuffalo = $buffaloes->firstWhere('id', $pregnancy->buffalo_id);
                                    @endphp

                                    <tr>

                                        <td data-label="પશુ નં">
                                            <select name="pregnancy_buffalo_id[]"
                                                class="form-control buffalo-select">

                                                <option value="">પશુ પસંદ કરો</option>

                                                @foreach($buffaloes as $buffalo)

                                                <option value="{{ $buffalo->id }}"
                                                    data-name="{{ $buffalo->name }}"
                                                    {{ $buffalo->id == $pregnancy->buffalo_id ? 'selected' : '' }}>

                                                    {{ $buffalo->tag_number }}

                                                </option>

                                                @endforeach

                                            </select>
                                        </td>

                                        <td data-label="પશુ નામ">
                                            <input type="text"
                                                class="form-control buffalo-name"
                                                value="{{ $selectedBuffalo->name ?? '' }}"
                                                readonly>
                                        </td>

                                        <td data-label="છેલ્લી હીટ">
                                            <input type="date"
                                                name="last_heat_date[]"
                                                class="form-control"
                                                value="{{ $pregnancy->buffalo->heat_date ?? '' }}">
                                        </td>

                                        <td data-label="AI તારીખ">
                                            <input type="date"
                                                name="ai_date[]"
                                                class="form-control"
                                                value="{{ $pregnancy->buffalo->ai_date ?? '' }}">
                                        </td>

                                        <td data-label="પ્રેગ્નન્ટ તારીખ">
                                            <input type="date"
                                                name="pregnant_date[]"
                                                class="form-control"
                                                value="{{ $pregnancy->buffalo->pregnancy_check_date ?? '' }}">
                                        </td>

                                        <td data-label="Expected Delivery">
                                            <input type="date"
                                                name="expected_delivery[]"
                                                class="form-control"
                                                value="{{ $pregnancy->buffalo->expected_delivery_date ?? '' }}">
                                        </td>

                                        <td class="text-center">
                                            <i class="fa-solid fa-circle-minus text-danger remove-pregnancy-row"
                                                style="font-size:22px;cursor:pointer;{{ $index == 0 ? 'display:none;' : '' }}"></i>
                                        </td>

                                    </tr>

                                    @endforeach

                                    @else

                                    {{-- Create Page Default Row --}}

                                    <tr>

                                        <td data-label="પશુ નં">
                                            <select name="pregnancy_buffalo_id[]"
                                                class="form-control buffalo-select">

                                                <option value="">પશુ પસંદ કરો</option>

                                                @foreach($buffaloes as $buffalo)

                                                <option value="{{ $buffalo->id }}"
                                                    data-name="{{ $buffalo->name }}">

                                                    {{ $buffalo->tag_number }}

                                                </option>

                                                @endforeach

                                            </select>
                                        </td>

                                        <td data-label="પશુ નામ">
                                            <input type="text"
                                                class="form-control buffalo-name"
                                                readonly>
                                        </td>

                                        <td data-label="છેલ્લી હીટ">
                                            <input type="date"
                                                name="last_heat_date[]"
                                                class="form-control">
                                        </td>

                                        <td data-label="AI તારીખ">
                                            <input type="date"
                                                name="ai_date[]"
                                                class="form-control">
                                        </td>

                                        <td data-label="પ્રેગ્નન્ટ તારીખ">
                                            <input type="date"
                                                name="pregnant_date[]"
                                                class="form-control">
                                        </td>

                                        <td data-label="Expected Delivery">
                                            <input type="date"
                                                name="expected_delivery[]"
                                                class="form-control">
                                        </td>

                                        <td class="text-center">
                                            <i class="fa-solid fa-circle-minus text-danger remove-pregnancy-row"
                                                style="display:none;font-size:22px;cursor:pointer;"></i>
                                        </td>

                                    </tr>

                                    @endif

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Cleaning -->
        <br>
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-secondary text-white">
                🧹 સફાઈ કામગીરી
            </div>
            <div class="card-body">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th data-label="કાર્ય">કાર્ય</th>
                            <th data-label="કર્યું?">કર્યું?</th>
                            <th data-label="કોણે કર્યું?">કોણે કર્યું?</th>
                            <th data-label="નોંધ">નોંધ</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>ગૌશાળા સાફ</td>

                            <td>
                                <input type="checkbox"
                                    name="clean_cowshed"
                                    value="1"
                                    {{ old('clean_cowshed', $report->clean_cowshed ?? 0) ? 'checked' : '' }}>
                            </td>

                            <td>
                                <select name="clean_cowshed_by" class="form-control">

                                    <option value="">કર્મચારી પસંદ કરો</option>

                                    @foreach($employees as $employee)

                                    <option value="{{ $employee->id }}"
                                        {{ old('clean_cowshed_by', $report->clean_cowshed_by ?? '') == $employee->id ? 'selected' : '' }}>

                                        {{ $employee->name }}

                                    </option>

                                    @endforeach

                                </select>
                            </td>

                            <td>
                                <input type="text"
                                    name="clean_cowshed_note"
                                    class="form-control"
                                    value="{{ old('clean_cowshed_note', $report->clean_cowshed_note ?? '') }}"
                                    placeholder="Enter Notes">
                            </td>
                        </tr>

                        <tr>
                            <td>દૂધ રૂમ સાફ</td>

                            <td>
                                <input type="checkbox"
                                    name="clean_milk_room"
                                    value="1"
                                    {{ old('clean_milk_room', $report->clean_milk_room ?? 0) ? 'checked' : '' }}>
                            </td>

                            <td>
                                <select name="clean_milk_room_by" class="form-control">

                                    <option value="">કર્મચારી પસંદ કરો</option>

                                    @foreach($employees as $employee)

                                    <option value="{{ $employee->id }}"
                                        {{ old('clean_milk_room_by', $report->clean_milk_room_by ?? '') == $employee->id ? 'selected' : '' }}>

                                        {{ $employee->name }}

                                    </option>

                                    @endforeach

                                </select>
                            </td>

                            <td>
                                <input type="text"
                                    name="clean_milk_room_note"
                                    class="form-control"
                                    value="{{ old('clean_milk_room_note', $report->clean_milk_room_note ?? '') }}"
                                    placeholder="Enter Notes">
                            </td>
                        </tr>

                        <tr>
                            <td>સ્ટોર સાફ</td>

                            <td>
                                <input type="checkbox"
                                    name="clean_store"
                                    value="1"
                                    {{ old('clean_store', $report->clean_store ?? 0) ? 'checked' : '' }}>
                            </td>

                            <td>
                                <select name="clean_store_by" class="form-control">

                                    <option value="">કર્મચારી પસંદ કરો</option>

                                    @foreach($employees as $employee)

                                    <option value="{{ $employee->id }}"
                                        {{ old('clean_store_by', $report->clean_store_by ?? '') == $employee->id ? 'selected' : '' }}>

                                        {{ $employee->name }}

                                    </option>

                                    @endforeach

                                </select>
                            </td>

                            <td>
                                <input type="text"
                                    name="clean_store_note"
                                    class="form-control"
                                    value="{{ old('clean_store_note', $report->clean_store_note ?? '') }}"
                                    placeholder="Enter Notes">
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>



        </div>

        <!--  Expense Section -->
        <br>
        <div class="card">
            <div class="card-header bg-danger text-white"
                style="display:flex;justify-content:space-between;align-items:center;">

                <span>💰 ખર્ચ</span>

                <div>
                    <i class="fa-solid fa-circle-plus text-success"
                        id="addExpenseRow"
                        style="color:#198754;font-size:22px;cursor:pointer;"></i>


                </div>

            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>ખર્ચનું નામ</th>
                        <th>રકમ</th>
                        <th>નોંધ</th>
                    </tr>
                </thead>

                <tbody id="expenseBody">

                    @if(isset($report) && $report->expenses->count())

                    @foreach($report->expenses as $index => $expense)

                    <tr>

                        <td>
                            <input type="text"
                                name="expense_title[]"
                                class="form-control"
                                value="{{ $expense->title }}"
                                placeholder="Enter Expense Title">
                        </td>

                        <td>
                            <input type="number"
                                step="0.01"
                                name="expense_amount[]"
                                class="form-control"
                                value="{{ $expense->amount }}"
                                placeholder="Enter Expense Amount">
                        </td>

                        <td>
                            <input type="text"
                                name="expense_remarks[]"
                                class="form-control"
                                value="{{ $expense->remarks }}"
                                placeholder="Enter Remarks">
                        </td>


                        <td class="text-center">
                            <i class="fa-solid fa-circle-minus text-danger remove-expense-row"
                                style="font-size:22px;cursor:pointer;{{ $index == 0 ? 'display:none;' : '' }}"></i>
                        </td>

                    </tr>

                    @endforeach

                    @else

                    {{-- Create Page Default Row --}}

                    <tr>

                        <td>
                            <input type="text"
                                name="expense_title[]"
                                class="form-control"
                                placeholder="Enter Expense Title">
                        </td>

                        <td>
                            <input type="number"
                                step="0.01"
                                name="expense_amount[]"
                                class="form-control"
                                placeholder="Enter Expense Amount">
                        </td>

                        <td>
                            <input type="text"
                                name="expense_remarks[]"
                                class="form-control"
                                placeholder="Enter Remarks">
                        </td>

                        <td class="text-center">
                            <i class="fa-solid fa-circle-minus text-danger remove-expense-row"
                                style="display:none;font-size:22px;cursor:pointer;"></i>
                        </td>

                    </tr>

                    @endif

                </tbody>
            </table>
        </div>

        <!-- Income Section -->
        <br>
        <div class="card">

            <div class="card-header bg-danger text-white"
                style="display:flex;justify-content:space-between;align-items:center;">

                <span>💵 આવક</span>

                <div>
                    <i class="fa-solid fa-circle-plus text-success"
                        id="addIncomeRow"
                        style="color:#198754;font-size:22px;cursor:pointer;"></i>


                </div>

            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>આવકનું નામ</th>
                        <th>રકમ</th>
                        <th>નોંધ</th>
                    </tr>
                </thead>

                <tbody id="incomeBody">

                    @if(isset($report) && $report->incomes->count())

                    @foreach($report->incomes as $index => $income)

                    <tr>

                        <td>
                            <input type="text"
                                name="income_title[]"
                                class="form-control"
                                value="{{ $income->title }}"
                                placeholder="Enter Income Title">
                        </td>

                        <td>
                            <input type="number"
                                step="0.01"
                                name="income_amount[]"
                                class="form-control"
                                value="{{ $income->amount }}"
                                placeholder="Enter Income Amount">
                        </td>

                        <td>
                            <input type="text"
                                name="income_remarks[]"
                                class="form-control"
                                value="{{ $income->remarks }}"
                                placeholder="Enter Remarks">
                        </td>

                        <td class="text-center" style="width:60px;">
                            <i class="fa-solid fa-circle-minus text-danger remove-income-row"
                                @if($index==0)
                                style="display:none;font-size:22px;cursor:pointer;"
                                @else
                                style="font-size:22px;cursor:pointer;"
                                @endif>
                            </i>
                        </td>

                    </tr>

                    @endforeach

                    @else

                    {{-- Create Page Default Row --}}

                    <tr>

                        <td>
                            <input type="text"
                                name="income_title[]"
                                class="form-control"
                                placeholder="Enter Income Title">
                        </td>

                        <td>
                            <input type="number"
                                step="0.01"
                                name="income_amount[]"
                                class="form-control"
                                placeholder="Enter Income Amount">
                        </td>

                        <td>
                            <input type="text"
                                name="income_remarks[]"
                                class="form-control"
                                placeholder="Enter Remarks">
                        </td>

                        <td class="text-center" style="width:60px;">
                            <i class="fa-solid fa-circle-minus text-danger remove-income-row"
                                style="display:none;font-size:22px;cursor:pointer;">
                            </i>
                        </td>

                    </tr>

                    @endif

                </tbody>
            </table>
        </div>

        <!-- Notes -->
        <br>
        <div class="card shadow-sm border-0">

            <div class="card-header bg-info text-white">
                📝 ખાસ નોંધ
            </div>

            <div class="card-body">

                <textarea
                    name="notes"
                    class="form-control"
                    rows="5"
                    placeholder="Enter Notes">{{ old('notes', $report->notes ?? '') }}</textarea>

            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-success">
                    💾 સેવ કરો
                </button>
            </div>

        </div>


        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script>
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('buffalo-select')) {
                    let row = e.target.closest('tr');
                    let selected = e.target.options[e.target.selectedIndex];
                    let buffaloName = selected.getAttribute('data-name') || '';
                    row.querySelector('.buffalo-name').value = buffaloName;
                }
            });
        </script>
        <script>
            document.addEventListener('input', function(e) {

                let row = e.target.closest('tr');
                if (!row) return;

                let morning = parseFloat(
                    row.querySelector('.morning')?.value || 0
                );

                let evening = parseFloat(
                    row.querySelector('.evening')?.value || 0
                );

                let total = row.querySelector('.total');

                if (total) {
                    total.value = (morning + evening).toFixed(2);
                }
            });
        </script>

        <!-- staffBody -->

        <script>
            function toggleStaffMinus() {

                let rows = document.querySelectorAll('#staffBody tr');

                rows.forEach((row, index) => {

                    let minus = row.querySelector('.remove-staff-row');

                    if (rows.length === 1) {
                        minus.style.display = 'none';
                    } else {
                        minus.style.display = 'inline-block';
                    }

                });
            }

            document.getElementById('addStaffRow').addEventListener('click', function() {

                let firstRow = document.querySelector('#staffBody tr');
                let newRow = firstRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

                document.getElementById('staffBody').appendChild(newRow);

                toggleStaffMinus();
            });

            document.addEventListener('click', function(e) {

                const removeBtn = e.target.closest('.remove-staff-row');

                if (removeBtn) {

                    removeBtn.closest('tr').remove();

                    toggleStaffMinus();
                }
            });

            toggleStaffMinus();
        </script>

        <!-- milkBody -->

        <script>
            function toggleMilkMinus() {

                let rows = document.querySelectorAll('#milkBody tr');

                rows.forEach((row, index) => {

                    let minus = row.querySelector('.remove-milk-row');

                    if (rows.length === 1) {
                        minus.style.display = 'none';
                    } else {
                        minus.style.display = 'inline-block';
                    }

                });
            }

            document.getElementById('addMilkRow').addEventListener('click', function() {

                let firstRow = document.querySelector('#milkBody tr');
                let newRow = firstRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

                document.getElementById('milkBody').appendChild(newRow);

                toggleMilkMinus();
            });

            document.addEventListener('click', function(e) {

                const removeBtn = e.target.closest('.remove-milk-row');

                if (removeBtn) {

                    removeBtn.closest('tr').remove();

                    toggleMilkMinus();
                }
            });

            toggleMilkMinus();
        </script>

        <!-- HealthBody -->

        <script>
            function toggleHealthMinus() {

                let rows = document.querySelectorAll('#healthBody tr');

                rows.forEach((row, index) => {

                    let minus = row.querySelector('.remove-health-row');

                    if (rows.length === 1) {
                        minus.style.display = 'none';
                    } else {
                        minus.style.display = 'inline-block';
                    }

                });
            }

            document.getElementById('addHealthRow').addEventListener('click', function() {

                let firstRow = document.querySelector('#healthBody tr');
                let newRow = firstRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

                document.getElementById('healthBody').appendChild(newRow);

                toggleHealthMinus();
            });

            document.addEventListener('click', function(e) {

                const removeBtn = e.target.closest('.remove-health-row');

                if (removeBtn) {

                    removeBtn.closest('tr').remove();

                    toggleHealthMinus();
                }
            });

            toggleHealthMinus();
            // ===== Dropdown Toggle =====
            document.getElementById('healthToggleIcon')
                .addEventListener('click', function() {

                    let content = document.getElementById('healthContent');

                    if (content.style.display === 'none' || content.style.display === '') {

                        content.style.display = 'block';

                        this.classList.remove('fa-chevron-down');
                        this.classList.add('fa-chevron-up');

                    } else {

                        content.style.display = 'none';

                        this.classList.remove('fa-chevron-up');
                        this.classList.add('fa-chevron-down');
                    }
                });
        </script>

        <!--VaccinationBody -->

        <script>
            function toggleVaccinationMinus() {

                let rows = document.querySelectorAll('#vaccinationBody tr');

                rows.forEach((row, index) => {

                    let minus = row.querySelector('.remove-vaccination-row');

                    if (rows.length === 1) {
                        minus.style.display = 'none';
                    } else {
                        minus.style.display = 'inline-block';
                    }

                });
            }

            document.getElementById('addVaccinationRow').addEventListener('click', function() {

                let firstRow = document.querySelector('#vaccinationBody tr');
                let newRow = firstRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

                document.getElementById('vaccinationBody').appendChild(newRow);

                toggleVaccinationMinus();
            });

            document.addEventListener('click', function(e) {

                const removeBtn = e.target.closest('.remove-vaccination-row');

                if (removeBtn) {

                    removeBtn.closest('tr').remove();

                    toggleVaccinationMinus();
                }
            });

            toggleVaccinationMinus();

            // ===== Dropdown Toggle =====
            document.getElementById('vaccinationToggleIcon')
                .addEventListener('click', function() {

                    let content = document.getElementById('vaccinationContent');

                    if (content.style.display === 'none' || content.style.display === '') {

                        content.style.display = 'block';

                        this.classList.remove('fa-chevron-down');
                        this.classList.add('fa-chevron-up');

                    } else {

                        content.style.display = 'none';

                        this.classList.remove('fa-chevron-up');
                        this.classList.add('fa-chevron-down');
                    }
                });
        </script>

        <!-- pregnancyBody -->

        <script>
            function togglePregnancyMinus() {

                let rows = document.querySelectorAll('#pregnancyBody tr');

                rows.forEach((row, index) => {

                    let minus = row.querySelector('.remove-pregnancy-row');

                    if (rows.length === 1) {
                        minus.style.display = 'none';
                    } else {
                        minus.style.display = 'inline-block';
                    }

                });
            }

            document.getElementById('addPregnancyRow').addEventListener('click', function() {

                let firstRow = document.querySelector('#pregnancyBody tr');
                let newRow = firstRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

                document.getElementById('pregnancyBody').appendChild(newRow);

                togglePregnancyMinus();
            });

            document.addEventListener('click', function(e) {

                const removeBtn = e.target.closest('.remove-pregnancy-row');

                if (removeBtn) {

                    removeBtn.closest('tr').remove();

                    togglePregnancyMinus();
                }
            });

            togglePregnancyMinus();
            // ===== Dropdown Toggle =====
            document.getElementById('pregnancyToggleIcon')
                .addEventListener('click', function() {

                    let content = document.getElementById('pregnancyContent');

                    if (content.style.display === 'none' || content.style.display === '') {

                        content.style.display = 'block';

                        this.classList.remove('fa-chevron-down');
                        this.classList.add('fa-chevron-up');

                    } else {

                        content.style.display = 'none';

                        this.classList.remove('fa-chevron-up');
                        this.classList.add('fa-chevron-down');
                    }
                });
        </script>

        <!-- ExpenseBody -->

        <script>
            function toggleExpenseMinus() {

                let rows = document.querySelectorAll('#expenseBody tr');

                rows.forEach((row, index) => {

                    let minus = row.querySelector('.remove-expense-row');

                    if (rows.length === 1) {
                        minus.style.display = 'none';
                    } else {
                        minus.style.display = 'inline-block';
                    }

                });
            }

            document.getElementById('addExpenseRow').addEventListener('click', function() {

                let firstRow = document.querySelector('#expenseBody tr');
                let newRow = firstRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

                document.getElementById('expenseBody').appendChild(newRow);

                toggleExpenseMinus();
            });

            document.addEventListener('click', function(e) {

                const removeBtn = e.target.closest('.remove-expense-row');

                if (removeBtn) {

                    removeBtn.closest('tr').remove();

                    toggleExpenseMinus();
                }
            });

            toggleExpenseMinus();
        </script>

        <!-- IncomeBody -->

        <script>
            function toggleIncomeMinus() {

                let rows = document.querySelectorAll('#incomeBody tr');

                rows.forEach((row, index) => {

                    let minus = row.querySelector('.remove-income-row');

                    if (rows.length === 1) {
                        minus.style.display = 'none';
                    } else {
                        minus.style.display = 'inline-block';
                    }

                });
            }

            document.getElementById('addIncomeRow').addEventListener('click', function() {

                let firstRow = document.querySelector('#incomeBody tr');
                let newRow = firstRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

                document.getElementById('incomeBody').appendChild(newRow);

                toggleIncomeMinus();
            });

            document.addEventListener('click', function(e) {

                const removeBtn = e.target.closest('.remove-income-row');

                if (removeBtn) {

                    removeBtn.closest('tr').remove();

                    toggleIncomeMinus();
                }
            });

            toggleIncomeMinus();
        </script>

        <!-- feedBody -->
        <script>
            function toggleFeedMinus() {

                let rows = document.querySelectorAll('#feedBody tr');

                rows.forEach((row, index) => {

                    let minus = row.querySelector('.remove-feed-row');

                    if (rows.length === 1) {
                        minus.style.display = 'none';
                    } else {
                        minus.style.display = 'inline-block';
                    }

                });
            }

            document.getElementById('addFeedRow').addEventListener('click', function() {

                let firstRow = document.querySelector('#feedBody tr');
                let newRow = firstRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

                document.getElementById('feedBody').appendChild(newRow);

                toggleFeedMinus();
            });

            document.addEventListener('click', function(e) {

                const removeBtn = e.target.closest('.remove-feed-row');

                if (removeBtn) {

                    removeBtn.closest('tr').remove();

                    toggleFeedMinus();
                }
            });

            toggleFeedMinus();
        </script>

</form>

@endsection