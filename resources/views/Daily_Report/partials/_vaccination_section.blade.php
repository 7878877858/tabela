@php
    $hasVaccination = isset($report) && $report->vaccinations->count() > 0;
    $vaccYes = old('has_vaccination', $hasVaccination ? 'yes' : 'no');
@endphp

<div class="card shadow-sm border-0 dr-section-card dr-step-section" id="vaccinationSection" data-dr-step="5">
    <div class="card-header p-0 dr-section-header">
        <div class="dr-collapsible-header-inner">
            <span class="dr-collapsible-title">💉 Vaccination Events</span>
        </div>
    </div>
    <div class="card-body dr-section-content">
        <div class="dr-event-gate" id="vaccinationEventGate">
            <p class="dr-event-gate__question mb-2">આજે રસીકરણ થયું?</p>
            <div class="dr-event-gate__choices" role="radiogroup" aria-label="Vaccination today">
                <label class="dr-event-choice">
                    <input type="radio" name="has_vaccination" value="no" {{ $vaccYes !== 'yes' ? 'checked' : '' }}>
                    <span>ના</span>
                </label>
                <label class="dr-event-choice">
                    <input type="radio" name="has_vaccination" value="yes" {{ $vaccYes === 'yes' ? 'checked' : '' }}>
                    <span>હા</span>
                </label>
            </div>
            <div class="dr-event-gate__action mt-3" id="vaccinationRevealWrap" hidden>
                <button type="button" class="btn btn-outline-primary btn-sm" id="vaccinationRevealBtn">
                    <i class="fa-solid fa-circle-plus"></i> રસીકરણ નોંધો
                </button>
            </div>
        </div>

        <div id="vaccinationEntryPanel" class="dr-event-panel mt-3" hidden>
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
                        @if($hasVaccination)
                            @foreach($report->vaccinations as $index => $vaccination)
                            <tr class="dr-dynamic-row">
                                <td data-label="પશુ">
                                    <x-animal-select name="vaccination_buffalo_id[]" :value="$vaccination->buffalo_id" />
                                </td>
                                <td data-label="રસી">
                                    <input type="text" name="vaccine_name[]" class="form-control" value="{{ $vaccination->vaccine_name ?? '' }}" placeholder="રસીનું નામ">
                                </td>
                                <td data-label="તારીખ">
                                    <input type="date" name="vaccination_date[]" class="form-control" value="{{ $vaccination->vaccination_date?->format('Y-m-d') ?? '' }}">
                                </td>
                                <td data-label="નોંધ">
                                    <input type="text" name="vaccination_remarks[]" class="form-control" value="{{ $vaccination->remarks ?? '' }}" placeholder="નોંધ">
                                </td>
                                <td class="dr-row-remove" data-label="">
                                    <button type="button" class="dr-remove-btn remove-vaccination-row" title="Remove row" aria-label="Remove row"{{ $index === 0 ? ' style="display:none;"' : '' }}>
                                        <i class="fa-solid fa-circle-minus text-danger"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <template id="vaccinationRowTemplate">
            <tr class="dr-dynamic-row">
                <td data-label="પશુ">
                    <x-animal-select name="vaccination_buffalo_id[]" />
                </td>
                <td data-label="રસી">
                    <input type="text" name="vaccine_name[]" class="form-control" placeholder="રસીનું નામ">
                </td>
                <td data-label="તારીખ">
                    <input type="date" name="vaccination_date[]" class="form-control" value="{{ old('report_date', $report->report_date ?? date('Y-m-d')) }}">
                </td>
                <td data-label="નોંધ">
                    <input type="text" name="vaccination_remarks[]" class="form-control" placeholder="નોંધ">
                </td>
                <td class="dr-row-remove" data-label="">
                    <button type="button" class="dr-remove-btn remove-vaccination-row" title="Remove row" aria-label="Remove row">
                        <i class="fa-solid fa-circle-minus text-danger"></i>
                    </button>
                </td>
            </tr>
        </template>
    </div>
</div>
