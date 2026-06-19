@php
    $hasHealth = isset($report) && $report->health->count() > 0;
    $healthYes = old('has_health', $hasHealth ? 'yes' : 'no');
@endphp

<div class="card shadow-sm border-0 dr-section-card dr-step-section" id="healthSection" data-dr-step="4">
    <div class="card-header p-0 dr-section-header">
        <div class="dr-collapsible-header-inner">
            <span class="dr-collapsible-title">🏥 Health Events</span>
        </div>
    </div>
    <div class="card-body dr-section-content">
        <div class="dr-event-gate" id="healthEventGate">
            <p class="dr-event-gate__question mb-2">આજે કોઈ પશુ બીમાર છે?</p>
            <div class="dr-event-gate__choices" role="radiogroup" aria-label="Health today">
                <label class="dr-event-choice">
                    <input type="radio" name="has_health" value="no" {{ $healthYes !== 'yes' ? 'checked' : '' }}>
                    <span>ના</span>
                </label>
                <label class="dr-event-choice">
                    <input type="radio" name="has_health" value="yes" {{ $healthYes === 'yes' ? 'checked' : '' }}>
                    <span>હા</span>
                </label>
            </div>
            <div class="dr-event-gate__action mt-3" id="healthRevealWrap" hidden>
                <button type="button" class="btn btn-outline-primary btn-sm" id="healthRevealBtn">
                    <i class="fa-solid fa-circle-plus"></i> સારવાર નોંધો
                </button>
            </div>
        </div>

        <div id="healthEntryPanel" class="dr-event-panel mt-3" hidden>
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
                        @if($hasHealth)
                            @foreach($report->health as $index => $health)
                            <tr class="dr-dynamic-row">
                                <td data-label="પશુ">
                                    <x-animal-select name="health_buffalo_id[]" :value="$health->buffalo_id" />
                                </td>
                                <td data-label="સમસ્યા">
                                    <input type="text" name="health_issue[]" class="form-control" value="{{ $health->health_issue ?? '' }}" placeholder="સમસ્યા">
                                </td>
                                <td data-label="સારવાર">
                                    <input type="text" name="treatment[]" class="form-control" value="{{ $health->treatment ?? '' }}" placeholder="સારવાર">
                                </td>
                                <td data-label="દવા ખર્ચ">
                                    <input type="number" step="0.01" name="medicine_cost[]" class="form-control" value="{{ $health->medicine_cost ?? '' }}" placeholder="0.00">
                                </td>
                                <td class="dr-row-remove" data-label="">
                                    <button type="button" class="dr-remove-btn remove-health-row" title="Remove row" aria-label="Remove row"{{ $index === 0 ? ' style="display:none;"' : '' }}>
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

        <template id="healthRowTemplate">
            <tr class="dr-dynamic-row">
                <td data-label="પશુ">
                    <x-animal-select name="health_buffalo_id[]" />
                </td>
                <td data-label="સમસ્યા">
                    <input type="text" name="health_issue[]" class="form-control" placeholder="સમસ્યા">
                </td>
                <td data-label="સારવાર">
                    <input type="text" name="treatment[]" class="form-control" placeholder="સારવાર">
                </td>
                <td data-label="દવા ખર્ચ">
                    <input type="number" step="0.01" name="medicine_cost[]" class="form-control" placeholder="0.00">
                </td>
                <td class="dr-row-remove" data-label="">
                    <button type="button" class="dr-remove-btn remove-health-row" title="Remove row" aria-label="Remove row">
                        <i class="fa-solid fa-circle-minus text-danger"></i>
                    </button>
                </td>
            </tr>
        </template>
    </div>
</div>
