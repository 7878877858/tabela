@extends('layouts.app')
@section('title', 'દૈનિક સ્ટાફ કાર્ય અહેવાલ')

@section('content')
@php
    $drCssVer = @filemtime(public_path('assets/css/daily-report.css')) ?: '1';
    $drJsVer = @filemtime(public_path('assets/js/daily-report-grids.js')) ?: '1';
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/daily-report.css') }}?v={{ $drCssVer }}">
<style>
    /* Page-specific: Daily Report mobile table cards — global styles in design-system.css */
    .icon {
        font-size: 24px;
        width: 24px;
        text-align: center;
    }

    /* Collapsible sections */
    .dr-collapsible-section.is-collapsed .dr-collapsible-content {
        display: none !important;
    }

    .dr-collapsible-header {
        cursor: pointer;
        user-select: none;
    }

    .dr-remove-btn {
        padding: 0;
        border: none;
        background: transparent;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin-left: auto;
    }

    .dr-remove-btn i {
        font-size: 1.125rem;
        pointer-events: none;
    }

    .dr-section-add-btn {
        padding: 0;
        border: none;
        background: transparent;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #198754;
        font-size: 1.125rem;
    }

    .dr-section-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
        flex-shrink: 0;
    }

    .dr-section-toolbar {
        padding: 10px 14px;
        border-bottom: 1px solid #e2e8f0;
    }

    @media(max-width:768px) {

        .daily-report-page .container-fluid {
            padding-left: 0;
            padding-right: 0;
        }

        .daily-report-page h1,
        .daily-report-page h2,
        .daily-report-page h3,
        .daily-report-page h4,
        .daily-report-page h5 {
            font-size: 1rem !important;
        }

        .daily-report-page .card-header {
            font-size: 0.8125rem;
        }

        .daily-report-page .btn-row .btn {
            width: auto;
            margin-top: 0;
        }

        /* Legacy tables only — exclude milk/feed grids and collapsible section tables */
        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table),
        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) thead,
        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) tbody,
        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) th,
        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) td,
        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) tr {
            display: block;
            width: 100%;
        }

        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) thead {
            display: none;
        }

        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) tr {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 8px;
            background: #fff;
            padding: 8px;
        }

        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) td::before {
            content: attr(data-label);
            display: block;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 2px;
            font-size: 0.6875rem;
        }

        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) td {
            border: none !important;
            padding: 4px 0 !important;
        }

        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) td .form-control,
        .daily-report-page table:not(.milk-grid-table):not(.feed-grid-table):not(.dr-section-table) td select {
            width: 100% !important;
            min-width: 100% !important;
        }

        .daily-report-page .fw-bold {
            font-size: 1.125rem !important;
        }

        input[type="date"],
        select.form-control,
        textarea {
            width: 100%;
        }

        .form-error-banner {
            display: none;
            margin-bottom: 12px;
            padding: 12px 14px;
            border-radius: 6px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 14px;
        }

        .form-error-banner ul {
            margin: 6px 0 0 18px;
            padding: 0;
        }

        .form-error-banner li {
            margin: 2px 0;
        }

        .form-control.is-invalid,
        select.is-invalid {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.15);
        }

    }
</style>
<form id="dailyReportForm"
    class="daily-report-page"
    data-is-edit="{{ isset($report) ? '1' : '0' }}"
    data-report-id="{{ $report->id ?? '' }}"
    action="{{ isset($report)
        ? route('daily-reports.update',$report->id)
        : route('daily-reports.store') }}"
    method="POST">

    @csrf

    @if(isset($report))
    @method('PUT')
    @endif

    <script type="application/json" id="dailyReportAnimalTypeCounts">@json($animalTypeCounts ?? \App\Models\Buffalo::activeCountsByAnimalType())</script>

    <div id="clientFormErrors" class="form-error-banner" role="alert" aria-live="polite"></div>

    <div class="container-fluid">


        <div class="card border-0 shadow-lg dr-page-hero">
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

        <nav class="dr-step-nav" aria-label="Daily Report steps">
            <a href="#basicInfoSection" class="dr-step-nav__item"><span class="dr-step-nav__num">1</span> મૂળભૂત</a>
            <a href="#milkSection" class="dr-step-nav__item"><span class="dr-step-nav__num">2</span> દૂધ</a>
            <a href="#feedSection" class="dr-step-nav__item"><span class="dr-step-nav__num">3</span> ચારો</a>
            <a href="#healthSection" class="dr-step-nav__item"><span class="dr-step-nav__num">4</span> આરોગ્ય</a>
            <a href="#vaccinationSection" class="dr-step-nav__item"><span class="dr-step-nav__num">5</span> રસી</a>
            <a href="#pregnancySection" class="dr-step-nav__item"><span class="dr-step-nav__num">6</span> ગર્ભ</a>
            <a href="#expenseSection" class="dr-step-nav__item"><span class="dr-step-nav__num">7</span> ખર્ચ</a>
            <a href="#incomeSection" class="dr-step-nav__item"><span class="dr-step-nav__num">8</span> આવક</a>
            <a href="#notesSection" class="dr-step-nav__item"><span class="dr-step-nav__num">9</span> નોંધ</a>
            <a href="#dr-step-save" class="dr-step-nav__item dr-step-nav__item--save"><span class="dr-step-nav__num">10</span> સેવ</a>
        </nav>

        <!-- Summary -->
        <div class="card shadow-sm border-0 dr-section-card">

            <div class="card-header p-0 dr-section-header">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">📊 આજનો સારાંશ</span>
                </div>
            </div>

            <div class="card-body dr-section-content dr-section-content--compact">

                <div class="dr-metrics-row">

                    <div class="dr-metric-card blue">
                        <span class="dr-metric-card__label">🐄 કુલ પશુ</span>
                        <span class="dr-metric-card__value">{{ isset($totalAnimals) ? $totalAnimals : 0 }}</span>
                    </div>

                    <div class="dr-metric-card green">
                        <span class="dr-metric-card__label">🥛 દૂધ આપતા</span>
                        <span class="dr-metric-card__value">{{ isset($lactatingAnimals) ? $lactatingAnimals : 0 }}</span>
                    </div>

                    <div class="dr-metric-card orange">
                        <span class="dr-metric-card__label">🤰 ગર્ભવતી</span>
                        <span class="dr-metric-card__value">{{ isset($pregnantAnimals) ? $pregnantAnimals : 0 }}</span>
                    </div>

                    <div class="dr-metric-card red">
                        <span class="dr-metric-card__label">❤️ હીટ માં પશુ</span>
                        <span class="dr-metric-card__value">{{ isset($heatAnimals) ? $heatAnimals : 0 }}</span>
                    </div>

                    <div class="dr-metric-card purple">
                        <span class="dr-metric-card__label">🥛 કુલ દૂધ</span>
                        <span class="dr-metric-card__value">{{ number_format($totalMilk ?? 0, 2) }}</span>
                    </div>

                </div>

            </div>

        </div>
        <!-- Basic Info -->
        <div class="card shadow-sm border-0 dr-section-card dr-step-section" id="basicInfoSection" data-dr-step="1">

            <div class="card-header p-0 dr-section-header">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">🏡 મૂળભૂત માહિતી</span>
                </div>
            </div>

            <div class="card-body dr-section-content">

                <div class="ds-form-grid ds-form-grid-2 dr-basic-info-grid">

                    <div>
                        <label data-label="તારીખ">તારીખ</label>
                        <input type="date"
                            name="report_date"
                            class="form-control"
                            value="{{ old('report_date', $report->report_date ?? date('Y-m-d')) }}"
                            required>
                    </div>

                    <div>
                        <label data-label="અહેવાલ નંબર">અહેવાલ નંબર</label>
                        <input type="text"
                            name="report_number"
                            class="form-control"
                            value="{{ old('report_number', $report->report_number ?? $reportNumber) }}"
                            readonly>
                    </div>

                    <div>
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

                    <div>
                        <label data-label="રિપોર્ટ બનાવનાર">રિપોર્ટ બનાવનાર</label>
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
        <br style="display:none;">
        <div class="card shadow-sm border-0 dr-section-card" id="staffSection">

            <div class="card-header p-0 dr-section-header">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">👨‍💼 સ્ટાફ હાજરી</span>
                    <button type="button" class="dr-section-add-btn" id="addStaffRow" title="પંક્તિ ઉમેરો" aria-label="પંક્તિ ઉમેરો">
                        <i class="fa-solid fa-circle-plus" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="card-body dr-section-content p-0">
                <div class="dr-section-table-area">
                <table class="table dr-section-table mb-0">
                    <thead>
                        <tr>
                            <th>કર્મચારી</th>
                            <th>હાજરી</th>
                            <th>નોંધ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="staffBody">
                        @if(isset($report) && $report->staff->count())
                        @foreach($report->staff as $index => $staff)
                        <tr class="dr-dynamic-row">
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

                            <td class="dr-row-remove" data-label="">
                                <button type="button" class="dr-remove-btn remove-staff-row" title="Remove row" aria-label="Remove row"{{ $index == 0 ? ' style="display:none;"' : '' }}>
                                    <i class="fa-solid fa-circle-minus text-danger"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach

                        @else

                        {{-- Create Page Row --}}
                        <tr class="dr-dynamic-row">
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

                            <td class="dr-row-remove" data-label="">
                                <button type="button" class="dr-remove-btn remove-staff-row" title="Remove row" aria-label="Remove row" style="display:none;">
                                    <i class="fa-solid fa-circle-minus text-danger"></i>
                                </button>
                            </td>
                        </tr>

                        @endif
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Milk Production -->
        <br style="display:none;">
        <div class="card shadow-sm border-0 dr-section-card dr-step-section" id="milkSection" data-dr-step="2">
            <div class="card-header p-0 dr-section-header">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">🥛 દૂધ ઉત્પાદન — એક પશુ = એક પંક્તિ (દુગ્ધારૂ)</span>
                </div>
            </div>
            <div class="card-body dr-section-content">
                @include('Daily_Report.partials._milk_grid')
            </div>
        </div>

        <!-- Feed Section -->
        <br style="display:none;">
        <div class="card shadow-sm border-0 dr-section-card dr-step-section" id="feedSection" data-dr-step="3">
            <div class="card-header p-0 dr-section-header">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">🌾 ચારો અને ખોરાક — એક પશુ = એક પંક્તિ</span>
                </div>
            </div>
            <div class="card-body dr-section-content">
                @error('feed_stock')
                <div class="form-error-banner" style="display:block;" role="alert">
                    <strong>ચારો સ્ટોક ભૂલ:</strong>
                    <ul><li>{{ $message }}</li></ul>
                </div>
                @enderror
                <div id="feedStockWarning" class="form-error-banner" role="alert" aria-live="polite"></div>
                @include('Daily_Report.partials._feed_grid')
            </div>
        </div>

        <!-- Health Section -->
        <br style="display:none;">
        <div class="card shadow-sm border-0 dr-section-card dr-collapsible-section is-collapsed dr-step-section" id="healthSection" data-dr-step="4">
            <div class="card-header dr-collapsible-header p-0" role="button" tabindex="0" aria-expanded="false">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">🏥 આરોગ્ય અને સારવાર</span>
                    <i class="fa-solid fa-chevron-down dr-section-toggle" id="healthToggleIcon" aria-hidden="true"></i>
                </div>
            </div>
            <div class="card-body dr-collapsible-content dr-section-content p-0" id="healthContent">
                <div class="dr-section-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-success" id="addHealthRow">
                        <i class="fa-solid fa-circle-plus"></i> પંક્તિ ઉમેરો
                    </button>
                </div>
                <div class="dr-section-table-area">
                <table class="table dr-section-table mb-0">
                    <thead>
                        <tr>
                            <th>પશુ</th>
                            <th>સમસ્યા</th>
                            <th>સારવાર</th>
                            <th>દવા ખર્ચ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="healthBody">

                            @if(isset($report) && $report->health->count())

                            @foreach($report->health as $index => $health)

                            <tr class="dr-dynamic-row">

                                <td data-label="પશુ">
                                    <select name="health_buffalo_id[]" class="form-control">

                                        <option value="">પશુ પસંદ કરો</option>

                                        @foreach($buffaloes as $buffalo)

                                        <option value="{{ $buffalo->id }}" data-animal-type="{{ $buffalo->animal_type ?? 'buffalo' }}"
                                            {{ $buffalo->id == $health->buffalo_id ? 'selected' : '' }}>

                                            {{ $buffalo->display_label }}

                                        </option>

                                        @endforeach

                                    </select>
                                </td>

                                <td data-label="સમસ્યા">
                                    <input type="text"
                                        name="health_issue[]"
                                        class="form-control"
                                        value="{{ $health->health_issue ?? '' }}"
                                        placeholder="Enter Health Issue">
                                </td>

                                <td data-label="સારવાર">
                                    <input type="text"
                                        name="treatment[]"
                                        class="form-control"
                                        value="{{ $health->treatment ?? '' }}"
                                        placeholder="Enter Treatment Details">
                                </td>

                                <td data-label="દવા ખર્ચ">
                                    <input type="number"
                                        step="0.01"
                                        name="medicine_cost[]"
                                        class="form-control"
                                        value="{{ $health->medicine_cost ?? '' }}"
                                        placeholder="Enter Medicine Cost">
                                </td>

                                <td class="dr-row-remove" data-label="">
                                    <button type="button" class="dr-remove-btn remove-health-row" title="Remove row" aria-label="Remove row"{{ $index == 0 ? ' style="display:none;"' : '' }}>
                                        <i class="fa-solid fa-circle-minus text-danger"></i>
                                    </button>
                                </td>

                            </tr>

                            @endforeach

                            @else

                            {{-- Create Page Default Row --}}

                            <tr class="dr-dynamic-row">

                                <td data-label="પશુ">
                                    <select name="health_buffalo_id[]" class="form-control">

                                        <option value="">પશુ પસંદ કરો</option>

                                        @foreach($buffaloes as $buffalo)

                                        <option value="{{ $buffalo->id }}" data-animal-type="{{ $buffalo->animal_type ?? 'buffalo' }}">
                                            {{ $buffalo->display_label }}
                                        </option>

                                        @endforeach

                                    </select>
                                </td>

                                <td data-label="સમસ્યા">
                                    <input type="text"
                                        name="health_issue[]"
                                        class="form-control"
                                        placeholder="Enter Health Issue">
                                </td>

                                <td data-label="સારવાર">
                                    <input type="text"
                                        name="treatment[]"
                                        class="form-control"
                                        placeholder="Enter Treatment Details">
                                </td>

                                <td data-label="દવા ખર્ચ">
                                    <input type="number"
                                        step="0.01"
                                        name="medicine_cost[]"
                                        class="form-control"
                                        placeholder="Enter Medicine Cost">
                                </td>

                                <td class="dr-row-remove" data-label="">
                                    <button type="button" class="dr-remove-btn remove-health-row" title="Remove row" aria-label="Remove row" style="display:none;">
                                        <i class="fa-solid fa-circle-minus text-danger"></i>
                                    </button>
                                </td>

                            </tr>

                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Vaccination Section -->
        <br style="display:none;">
        <div class="card shadow-sm border-0 dr-section-card dr-collapsible-section is-collapsed dr-step-section" id="vaccinationSection" data-dr-step="5">
            <div class="card-header dr-collapsible-header p-0" role="button" tabindex="0" aria-expanded="false">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">💉 રસીકરણ</span>
                    <i class="fa-solid fa-chevron-down dr-section-toggle" id="vaccinationToggleIcon" aria-hidden="true"></i>
                </div>
            </div>
            <div class="card-body dr-collapsible-content dr-section-content p-0" id="vaccinationContent">
                <div class="dr-section-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-success" id="addVaccinationRow">
                        <i class="fa-solid fa-circle-plus"></i> પંક્તિ ઉમેરો
                    </button>
                </div>
                <div class="dr-section-table-area">
                <table class="table dr-section-table mb-0">
                    <thead>
                        <tr>
                            <th>પશુ</th>
                            <th>રસી</th>
                            <th>તારીખ</th>
                            <th>નોંધ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="vaccinationBody">

                            @if(isset($report) && $report->vaccinations->count())

                            @foreach($report->vaccinations as $index => $vaccination)

                            <tr class="dr-dynamic-row">

                                <td data-label="પશુ">
                                    <select name="vaccination_buffalo_id[]" class="form-control">

                                        <option value="">પશુ પસંદ કરો</option>

                                        @foreach($buffaloes as $buffalo)

                                        <option value="{{ $buffalo->id }}" data-animal-type="{{ $buffalo->animal_type ?? 'buffalo' }}"
                                            {{ $buffalo->id == $vaccination->buffalo_id ? 'selected' : '' }}>

                                            {{ $buffalo->display_label }}

                                        </option>

                                        @endforeach

                                    </select>
                                </td>

                                <td data-label="રસી">
                                    <input type="text"
                                        name="vaccine_name[]"
                                        class="form-control"
                                        value="{{ $vaccination->vaccine_name ?? '' }}"
                                        placeholder="Enter Vaccine Name">
                                </td>

                                <td data-label="તારીખ">
                                    <input type="date"
                                        name="vaccination_date[]"
                                        class="form-control"
                                        value="{{ $vaccination->vaccination_date ?? '' }}">
                                </td>

                                <td data-label="નોંધ">
                                    <input type="text"
                                        name="vaccination_remarks[]"
                                        class="form-control"
                                        value="{{ $vaccination->remarks ?? '' }}"
                                        placeholder="Enter Vaccination Remarks">
                                </td>

                                <td class="dr-row-remove" data-label="">
                                    <button type="button" class="dr-remove-btn remove-vaccination-row" title="Remove row" aria-label="Remove row"{{ $index == 0 ? ' style="display:none;"' : '' }}>
                                        <i class="fa-solid fa-circle-minus text-danger"></i>
                                    </button>
                                </td>

                            </tr>

                            @endforeach

                            @else

                            {{-- Create Page Default Row --}}

                            <tr class="dr-dynamic-row">

                                <td data-label="પશુ">
                                    <select name="vaccination_buffalo_id[]" class="form-control">

                                        <option value="">પશુ પસંદ કરો</option>

                                        @foreach($buffaloes as $buffalo)

                                        <option value="{{ $buffalo->id }}" data-animal-type="{{ $buffalo->animal_type ?? 'buffalo' }}">
                                            {{ $buffalo->display_label }}
                                        </option>

                                        @endforeach

                                    </select>
                                </td>

                                <td data-label="રસી">
                                    <input type="text"
                                        name="vaccine_name[]"
                                        class="form-control"
                                        placeholder="Enter Vaccine Name">
                                </td>

                                <td data-label="તારીખ">
                                    <input type="date"
                                        name="vaccination_date[]"
                                        class="form-control">
                                </td>

                                <td data-label="નોંધ">
                                    <input type="text"
                                        name="vaccination_remarks[]"
                                        class="form-control"
                                        placeholder="Enter Vaccination Remarks">
                                </td>

                                <td class="dr-row-remove" data-label="">
                                    <button type="button" class="dr-remove-btn remove-vaccination-row" title="Remove row" aria-label="Remove row" style="display:none;">
                                        <i class="fa-solid fa-circle-minus text-danger"></i>
                                    </button>
                                </td>

                            </tr>

                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pregnancy -->
        <br style="display:none;">
        <div class="card shadow-sm border-0 dr-section-card dr-collapsible-section is-collapsed dr-step-section" id="pregnancySection" data-dr-step="6">
            <div class="card-header dr-collapsible-header p-0" role="button" tabindex="0" aria-expanded="false">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">🤰 પ્રજનન અને પ્રેગ્નન્સી</span>
                    <i class="fa-solid fa-chevron-down dr-section-toggle" id="pregnancyToggleIcon" aria-hidden="true"></i>
                </div>
            </div>
            <div class="card-body dr-collapsible-content dr-section-content p-0" id="pregnancyContent">
                <div class="dr-section-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-success" id="addPregnancyRow">
                        <i class="fa-solid fa-circle-plus"></i> પંક્તિ ઉમેરો
                    </button>
                </div>
                <div class="dr-section-table-area">
                <div class="table-responsive">
                    <table class="table dr-section-table pregnancy-table mb-0" id="pregnancyTable">
                                <thead>
                                    <tr>
                                        <th data-label="પશુ નં">પશુ નં</th>
                                        <th data-label="પશુ નામ">પશુ નામ</th>
                                        <th data-label="છેલ્લી હીટ">છેલ્લી હીટ તારીખ</th>
                                        <th data-label="AI તારીખ">AI તારીખ</th>
                                        <th data-label="પ્રેગ્નન્ટ તારીખ">પ્રેગ્નન્ટ તારીખ</th>
                                        <th data-label="Expected Delivery">અપેક્ષિત પ્રસૂતિ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="pregnancyBody">

                                    @if(isset($report) && $report->pregnancy->count())

                                    @foreach($report->pregnancy as $index => $pregnancy)

                                    @php
                                    $selectedBuffalo = $buffaloes->firstWhere('id', $pregnancy->buffalo_id);
                                    @endphp

                                    <tr class="dr-dynamic-row">

                                        <td data-label="પશુ નં">
                                            <select name="pregnancy_buffalo_id[]"
                                                class="form-control buffalo-select">

                                                <option value="">પશુ પસંદ કરો</option>

                                                @foreach($buffaloes as $buffalo)

                                                <option value="{{ $buffalo->id }}" data-animal-type="{{ $buffalo->animal_type ?? 'buffalo' }}"
                                                    data-name="{{ $buffalo->name }}"
                                                    {{ $buffalo->id == $pregnancy->buffalo_id ? 'selected' : '' }}>

                                                    {{ $buffalo->display_label }}

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

                                        <td data-label="અપેક્ષિત પ્રસૂતિ">
                                            <input type="date"
                                                name="expected_delivery[]"
                                                class="form-control"
                                                value="{{ $pregnancy->buffalo->expected_delivery_date ?? '' }}">
                                        </td>

                                        <td class="dr-row-remove" data-label="">
                                            <button type="button" class="dr-remove-btn remove-pregnancy-row" title="Remove row" aria-label="Remove row"{{ $index == 0 ? ' style="display:none;"' : '' }}>
                                                <i class="fa-solid fa-circle-minus text-danger"></i>
                                            </button>
                                        </td>

                                    </tr>

                                    @endforeach

                                    @else

                                    {{-- Create Page Default Row --}}

                                    <tr class="dr-dynamic-row">

                                        <td data-label="પશુ નં">
                                            <select name="pregnancy_buffalo_id[]"
                                                class="form-control buffalo-select">

                                                <option value="">પશુ પસંદ કરો</option>

                                                @foreach($buffaloes as $buffalo)

                                                <option value="{{ $buffalo->id }}" data-animal-type="{{ $buffalo->animal_type ?? 'buffalo' }}"
                                                    data-name="{{ $buffalo->name }}">

                                                    {{ $buffalo->display_label }}

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

                                        <td data-label="અપેક્ષિત પ્રસૂતિ">
                                            <input type="date"
                                                name="expected_delivery[]"
                                                class="form-control">
                                        </td>

                                        <td class="dr-row-remove" data-label="">
                                            <button type="button" class="dr-remove-btn remove-pregnancy-row" title="Remove row" aria-label="Remove row" style="display:none;">
                                                <i class="fa-solid fa-circle-minus text-danger"></i>
                                            </button>
                                        </td>

                                    </tr>

                                    @endif

                                </tbody>
                            </table>
                </div>
                </div>
            </div>
        </div>

        <!-- Cleaning -->
        <br style="display:none;">
        <div class="card shadow-sm border-0 dr-section-card dr-collapsible-section is-collapsed" id="cleaningSection">

            <div class="card-header dr-collapsible-header p-0 dr-section-header" role="button" tabindex="0" aria-expanded="false">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">🧹 સફાઈ કામગીરી</span>
                    <i class="fa-solid fa-chevron-down dr-section-toggle" id="cleaningToggleIcon" aria-hidden="true"></i>
                </div>
            </div>
            <div class="card-body dr-collapsible-content dr-section-content" id="cleaningContent">

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
        <br style="display:none;">
        <div class="card shadow-sm border-0 dr-section-card dr-collapsible-section is-collapsed dr-step-section" id="expenseSection" data-dr-step="7">
            <div class="card-header dr-collapsible-header p-0 dr-section-header" role="button" tabindex="0" aria-expanded="false">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">💰 ખર્ચ</span>
                    <div class="dr-section-header-actions">
                        <button type="button" class="dr-section-add-btn" id="addExpenseRow" title="પંક્તિ ઉમેરો" aria-label="પંક્તિ ઉમેરો">
                            <i class="fa-solid fa-circle-plus" aria-hidden="true"></i>
                        </button>
                        <i class="fa-solid fa-chevron-down dr-section-toggle" id="expenseToggleIcon" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            <div class="card-body dr-collapsible-content dr-section-content p-0" id="expenseContent">
                <div class="dr-section-table-area">
                <table class="table dr-section-table mb-0">
                    <thead>
                        <tr>
                            <th>ખર્ચનું નામ</th>
                            <th>રકમ</th>
                            <th>નોંધ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="expenseBody">
                    @if(isset($report) && $report->expenses->count())
                    @foreach($report->expenses as $index => $expense)
                    <tr class="dr-dynamic-row">
                        <td data-label="ખર્ચનું નામ">
                            <input type="text"
                                name="expense_title[]"
                                class="form-control"
                                value="{{ $expense->title }}"
                                placeholder="Enter Expense Title">
                        </td>
                        <td data-label="રકમ">
                            <input type="number"
                                step="0.01"
                                name="expense_amount[]"
                                class="form-control"
                                value="{{ $expense->amount }}"
                                placeholder="Enter Expense Amount">
                        </td>
                        <td data-label="નોંધ">
                            <input type="text"
                                name="expense_remarks[]"
                                class="form-control"
                                value="{{ $expense->remarks }}"
                                placeholder="Enter Remarks">
                        </td>
                        <td class="dr-row-remove" data-label="">
                            <button type="button" class="dr-remove-btn remove-expense-row" title="Remove row" aria-label="Remove row"{{ $index == 0 ? ' style="display:none;"' : '' }}>
                                <i class="fa-solid fa-circle-minus text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="dr-dynamic-row">
                        <td data-label="ખર્ચનું નામ">
                            <input type="text"
                                name="expense_title[]"
                                class="form-control"
                                placeholder="Enter Expense Title">
                        </td>
                        <td data-label="રકમ">
                            <input type="number"
                                step="0.01"
                                name="expense_amount[]"
                                class="form-control"
                                placeholder="Enter Expense Amount">
                        </td>
                        <td data-label="નોંધ">
                            <input type="text"
                                name="expense_remarks[]"
                                class="form-control"
                                placeholder="Enter Remarks">
                        </td>
                        <td class="dr-row-remove" data-label="">
                            <button type="button" class="dr-remove-btn remove-expense-row" title="Remove row" aria-label="Remove row" style="display:none;">
                                <i class="fa-solid fa-circle-minus text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @endif
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Income Section -->
        <br style="display:none;">
        <div class="card shadow-sm border-0 dr-section-card dr-collapsible-section is-collapsed dr-step-section" id="incomeSection" data-dr-step="8">
            <div class="card-header dr-collapsible-header p-0 dr-section-header" role="button" tabindex="0" aria-expanded="false">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">💵 આવક</span>
                    <div class="dr-section-header-actions">
                        <button type="button" class="dr-section-add-btn" id="addIncomeRow" title="પંક્તિ ઉમેરો" aria-label="પંક્તિ ઉમેરો">
                            <i class="fa-solid fa-circle-plus" aria-hidden="true"></i>
                        </button>
                        <i class="fa-solid fa-chevron-down dr-section-toggle" id="incomeToggleIcon" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            <div class="card-body dr-collapsible-content dr-section-content p-0" id="incomeContent">
                <div class="dr-section-table-area">
                <table class="table dr-section-table mb-0">
                    <thead>
                        <tr>
                            <th>આવકનું નામ</th>
                            <th>રકમ</th>
                            <th>નોંધ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="incomeBody">
                    @if(isset($report) && $report->incomes->count())
                    @foreach($report->incomes as $index => $income)
                    <tr class="dr-dynamic-row">
                        <td data-label="આવકનું નામ">
                            <input type="text"
                                name="income_title[]"
                                class="form-control"
                                value="{{ $income->title }}"
                                placeholder="Enter Income Title">
                        </td>
                        <td data-label="રકમ">
                            <input type="number"
                                step="0.01"
                                name="income_amount[]"
                                class="form-control"
                                value="{{ $income->amount }}"
                                placeholder="Enter Income Amount">
                        </td>
                        <td data-label="નોંધ">
                            <input type="text"
                                name="income_remarks[]"
                                class="form-control"
                                value="{{ $income->remarks }}"
                                placeholder="Enter Remarks">
                        </td>
                        <td class="dr-row-remove" data-label="">
                            <button type="button" class="dr-remove-btn remove-income-row" title="Remove row" aria-label="Remove row"{{ $index == 0 ? ' style="display:none;"' : '' }}>
                                <i class="fa-solid fa-circle-minus text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="dr-dynamic-row">
                        <td data-label="આવકનું નામ">
                            <input type="text"
                                name="income_title[]"
                                class="form-control"
                                placeholder="Enter Income Title">
                        </td>
                        <td data-label="રકમ">
                            <input type="number"
                                step="0.01"
                                name="income_amount[]"
                                class="form-control"
                                placeholder="Enter Income Amount">
                        </td>
                        <td data-label="નોંધ">
                            <input type="text"
                                name="income_remarks[]"
                                class="form-control"
                                placeholder="Enter Remarks">
                        </td>
                        <td class="dr-row-remove" data-label="">
                            <button type="button" class="dr-remove-btn remove-income-row" title="Remove row" aria-label="Remove row" style="display:none;">
                                <i class="fa-solid fa-circle-minus text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @endif
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <br style="display:none;">
        <div class="card shadow-sm border-0 dr-section-card dr-notes-card dr-step-section" id="notesSection" data-dr-step="9">
            <div class="card-header p-0 dr-section-header">
                <div class="dr-collapsible-header-inner">
                    <span class="dr-collapsible-title">📝 ખાસ નોંધ</span>
                </div>
            </div>
            <div class="card-body dr-section-content">

                <textarea
                    name="notes"
                    class="form-control"
                    rows="3"
                    placeholder="Enter Notes">{{ old('notes', $report->notes ?? '') }}</textarea>

            </div>

            <div class="card-footer dr-save-footer" id="dr-step-save">
                <button type="submit" class="btn btn-success dr-save-btn">
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

        <script src="{{ asset('assets/js/daily-report-grids.js') }}?v={{ $drJsVer }}"></script>
        <script>
        (function() {
            function collectMilkGridForServer() {
                window.DailyReportMilkPager?.sync();
                const grid = {};
                document.getElementById('milkGridHiddenStore')?.querySelectorAll('[data-buffalo-id][data-period]').forEach((input) => {
                    const id = input.dataset.buffaloId;
                    const period = input.dataset.period;
                    if (!grid[id]) grid[id] = {};
                    grid[id][period] = input.value;
                });
                return grid;
            }

            let autosaveTimer;
            function scheduleServerMilkAutosave() {
                const toggle = document.getElementById('milkAutosaveToggle');
                const meta = document.getElementById('milkAutosaveMeta');
                const status = document.getElementById('milkAutosaveStatus');
                if (!meta || (toggle && !toggle.checked)) return;

                clearTimeout(autosaveTimer);
                if (status) { status.textContent = 'સર્વર સેવ...'; status.className = 'milk-autosave-status saving'; }

                autosaveTimer = setTimeout(async () => {
                    try {
                        const res = await fetch(meta.dataset.url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': meta.dataset.csrf,
                            },
                            body: JSON.stringify({
                                milk_grid: collectMilkGridForServer(),
                                report_date: document.querySelector('[name="report_date"]')?.value || null,
                            }),
                        });
                        if (!res.ok) throw new Error('fail');
                        if (status) { status.textContent = 'સર્વર સેવ ✓'; status.className = 'milk-autosave-status saved'; }
                    } catch (e) {
                        if (status) { status.textContent = 'સર્વર સેવ ભૂલ'; status.className = 'milk-autosave-status error'; }
                    }
                }, 1500);
            }

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('milk-qty')) {
                    window.DailyReportDraft?.scheduleSave();
                    scheduleServerMilkAutosave();
                }
                if (e.target.classList.contains('feed-qty')) {
                    window.DailyReportDraft?.scheduleSave();
                }
            });

            document.getElementById('dailyReportForm')?.addEventListener('submit', function() {
                window.DailyReportMilkPager?.sync();
                window.DailyReportFeedPager?.sync();
            });

            const stepNav = document.querySelector('.dr-step-nav');
            const stepSections = document.querySelectorAll('.dr-step-section[data-dr-step]');
            if (stepNav && stepSections.length) {
                const stepLinks = stepNav.querySelectorAll('.dr-step-nav__item[href^="#"]');
                const onScroll = () => {
                    let current = null;
                    const offset = 140;
                    stepSections.forEach((section) => {
                        if (section.getBoundingClientRect().top <= offset) {
                            current = section;
                        }
                    });
                    stepLinks.forEach((link) => {
                        const id = (link.getAttribute('href') || '').slice(1);
                        link.classList.toggle('is-active', current && current.id === id);
                    });
                };
                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();
            }
        })();
        </script>

        <!-- staffBody -->

        <script>
            function toggleStaffMinus() {

                let rows = document.querySelectorAll('#staffBody tr.dr-dynamic-row');

                rows.forEach((row) => {

                    let minus = row.querySelector('.remove-staff-row');

                    if (minus) {
                        minus.style.display = rows.length === 1 ? 'none' : 'inline-flex';
                    }

                });
            }

            document.getElementById('addStaffRow').addEventListener('click', function(e) {
                e.stopPropagation();

                let firstRow = document.querySelector('#staffBody tr.dr-dynamic-row');
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

        <!-- HealthBody -->

        <script>
            function toggleHealthMinus() {

                let rows = document.querySelectorAll('#healthBody tr');

                rows.forEach((row) => {

                    let minus = row.querySelector('.remove-health-row');

                    if (minus) {
                        minus.style.display = rows.length === 1 ? 'none' : 'inline-flex';
                    }

                });
            }

            document.getElementById('addHealthRow').addEventListener('click', function(e) {
                e.stopPropagation();

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
        </script>

        <!--VaccinationBody -->

        <script>
            function toggleVaccinationMinus() {

                let rows = document.querySelectorAll('#vaccinationBody tr');

                rows.forEach((row) => {

                    let minus = row.querySelector('.remove-vaccination-row');

                    if (minus) {
                        minus.style.display = rows.length === 1 ? 'none' : 'inline-flex';
                    }

                });
            }

            document.getElementById('addVaccinationRow').addEventListener('click', function(e) {
                e.stopPropagation();

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
        </script>

        <!-- pregnancyBody -->

        <script>
            function togglePregnancyMinus() {

                let rows = document.querySelectorAll('#pregnancyBody tr');

                rows.forEach((row) => {

                    let minus = row.querySelector('.remove-pregnancy-row');

                    if (minus) {
                        minus.style.display = rows.length === 1 ? 'none' : 'inline-flex';
                    }

                });
            }

            document.getElementById('addPregnancyRow').addEventListener('click', function(e) {
                e.stopPropagation();

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
        </script>

        <!-- Collapsible sections (Health / Vaccination / Pregnancy) -->
        <script>
        (function () {
            function isMobile() {
                return window.matchMedia('(max-width: 767.98px)').matches;
            }

            function updateToggleIcon(icon, expanded) {
                if (!icon) return;
                icon.classList.remove('fa-circle-plus', 'fa-chevron-up', 'fa-chevron-down', 'fa-circle-minus');
                if (isMobile()) {
                    icon.classList.add(expanded ? 'fa-chevron-up' : 'fa-chevron-down');
                } else {
                    icon.classList.add(expanded ? 'fa-chevron-up' : 'fa-chevron-down');
                }
            }

            function initCollapsibleSection(sectionEl, contentId, toggleId, options) {
                options = options || {};
                const content = document.getElementById(contentId);
                const toggle = toggleId ? document.getElementById(toggleId) : null;
                const header = sectionEl.querySelector('.dr-collapsible-header');
                if (!content || !header) return;

                function setExpanded(expanded) {
                    sectionEl.classList.toggle('is-collapsed', !expanded);
                    header.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    updateToggleIcon(toggle, expanded);
                }

                function toggleExpand() {
                    setExpanded(sectionEl.classList.contains('is-collapsed'));
                }

                setExpanded(false);

                header.addEventListener('click', function (e) {
                    if (options.ignoreClick && options.ignoreClick(e)) {
                        return;
                    }
                    toggleExpand();
                });

                header.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        toggleExpand();
                    }
                });

                if (toggle) {
                    toggle.addEventListener('click', function (e) {
                        e.stopPropagation();
                        toggleExpand();
                    });
                }
            }

            const sections = [
                ['healthSection', 'healthContent', 'healthToggleIcon'],
                ['vaccinationSection', 'vaccinationContent', 'vaccinationToggleIcon'],
                ['pregnancySection', 'pregnancyContent', 'pregnancyToggleIcon'],
                ['cleaningSection', 'cleaningContent', 'cleaningToggleIcon'],
                ['expenseSection', 'expenseContent', 'expenseToggleIcon', function (e) {
                    return e.target.closest('.dr-section-add-btn');
                }],
                ['incomeSection', 'incomeContent', 'incomeToggleIcon', function (e) {
                    return e.target.closest('.dr-section-add-btn');
                }],
            ];

            sections.forEach(function (cfg) {
                const sectionEl = document.getElementById(cfg[0]);
                if (sectionEl) {
                    initCollapsibleSection(sectionEl, cfg[1], cfg[2], {
                        ignoreClick: typeof cfg[3] === 'function' ? cfg[3] : null,
                    });
                }
            });
        })();
        </script>

        <!-- ExpenseBody -->

        <script>
            function toggleExpenseMinus() {

                let rows = document.querySelectorAll('#expenseBody tr.dr-dynamic-row');

                rows.forEach((row) => {

                    let minus = row.querySelector('.remove-expense-row');

                    if (minus) {
                        minus.style.display = rows.length === 1 ? 'none' : 'inline-flex';
                    }

                });
            }

            document.getElementById('addExpenseRow').addEventListener('click', function(e) {
                e.stopPropagation();

                let firstRow = document.querySelector('#expenseBody tr.dr-dynamic-row');
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

                let rows = document.querySelectorAll('#incomeBody tr.dr-dynamic-row');

                rows.forEach((row) => {

                    let minus = row.querySelector('.remove-income-row');

                    if (minus) {
                        minus.style.display = rows.length === 1 ? 'none' : 'inline-flex';
                    }

                });
            }

            document.getElementById('addIncomeRow').addEventListener('click', function(e) {
                e.stopPropagation();

                let firstRow = document.querySelector('#incomeBody tr.dr-dynamic-row');
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

        <!-- feed stock validation: show on problem, auto-clear when user fixes -->
        <script>
            const FormErrors = {
                show(bannerId, messages) {
                    const el = document.getElementById(bannerId);
                    if (!el) return;

                    const list = (Array.isArray(messages) ? messages : [messages]).filter(Boolean);
                    if (!list.length) {
                        this.hide(bannerId);
                        return;
                    }

                    el.innerHTML = '<strong>કૃપા કરીને સુધારો:</strong><ul>'
                        + list.map(m => `<li>${m}</li>`).join('')
                        + '</ul>';
                    el.style.display = 'block';
                },

                hide(bannerId) {
                    const el = document.getElementById(bannerId);
                    if (!el) return;
                    el.style.display = 'none';
                    el.innerHTML = '';
                },

                setInvalid(field, invalid) {
                    if (!field) return;
                    field.classList.toggle('is-invalid', !!invalid);
                },

                scrollToFirst() {
                    const banners = ['clientFormErrors', 'feedStockWarning'];
                    for (const id of banners) {
                        const el = document.getElementById(id);
                        if (el && el.style.display !== 'none' && el.innerHTML.trim()) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            return;
                        }
                    }
                }
            };

            function validateFeedStock() {
                const messages = [];
                const totals = {};

                document.querySelectorAll('.feed-qty').forEach(input => {
                    FormErrors.setInvalid(input, false);
                });

                document.querySelectorAll('.feed-qty').forEach(input => {
                    const feedId = input.dataset.feedId;
                    const qty = parseFloat(input.value) || 0;
                    if (qty <= 0 || !feedId) {
                        return;
                    }

                    totals[feedId] = (totals[feedId] || 0) + qty;
                });

                const stockMeta = {};
                document.querySelectorAll('.feed-stock-meta').forEach(meta => {
                    stockMeta[meta.dataset.feedId] = {
                        stock: parseFloat(meta.dataset.stock || 0) || 0,
                        label: meta.dataset.label || ('Feed ' + meta.dataset.feedId),
                    };
                });

                let ok = true;

                Object.entries(totals).forEach(([feedId, required]) => {
                    const meta = stockMeta[feedId] || { stock: 0, label: 'Feed ' + feedId };
                    if (required > meta.stock + 1e-9) {
                        ok = false;
                        messages.push(`${meta.label}: જરૂરી ${required.toFixed(2)} > ઉપલબ્ધ ${meta.stock.toFixed(2)}`);
                    }
                });

                if (!ok) {
                    document.querySelectorAll('.feed-qty').forEach(input => {
                        const feedId = input.dataset.feedId;
                        const qty = parseFloat(input.value) || 0;
                        if (qty <= 0) {
                            return;
                        }
                        const meta = stockMeta[feedId];
                        const totalRequired = totals[feedId] || 0;
                        if (meta && totalRequired > meta.stock + 1e-9) {
                            FormErrors.setInvalid(input, true);
                        }
                    });

                    FormErrors.show('feedStockWarning', messages);
                } else {
                    FormErrors.hide('feedStockWarning');
                }

                return ok;
            }

            function runClientValidation() {
                const feedOk = validateFeedStock();
                const allOk = feedOk;

                if (!allOk) {
                    FormErrors.show('clientFormErrors', ['ફોર્મમાં ભૂલ છે. નીચેના વિભાગો તપાસો.']);
                } else {
                    FormErrors.hide('clientFormErrors');
                }

                return allOk;
            }

            document.addEventListener('input', function(e) {
                if (e.target?.classList?.contains('feed-qty') || e.target?.closest?.('#feedGridTable')) {
                    runClientValidation();
                }
            });

            document.addEventListener('change', function(e) {
                if (e.target?.classList?.contains('feed-qty') || e.target?.closest?.('#feedGridTable')) {
                    runClientValidation();
                }
            });

            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (!form?.matches?.('form')) return;

                if (!runClientValidation()) {
                    e.preventDefault();
                    FormErrors.scrollToFirst();
                }
            }, true);

            runClientValidation();
        </script>

</form>

@include('Daily_Report.partials._draft_backup')

@endsection