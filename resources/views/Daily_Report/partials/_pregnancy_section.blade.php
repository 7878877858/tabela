@php
    $pregActivities = old('pregnancy_activities', []);
    if (empty($pregActivities) && isset($report) && $report->pregnancy->count()) {
        $pregActivities = ['pregcheck'];
    }
    $pregActivities = is_array($pregActivities) ? $pregActivities : [];
    $reportDate = old('report_date', isset($report) ? $report->report_date?->format('Y-m-d') : date('Y-m-d'));
@endphp

<div class="card shadow-sm border-0 dr-section-card dr-step-section" id="pregnancySection" data-dr-step="6">
    <div class="card-header p-0 dr-section-header">
        <div class="dr-collapsible-header-inner">
            <span class="dr-collapsible-title">🤰 Pregnancy Events</span>
        </div>
    </div>
    <div class="card-body dr-section-content">
        <p class="dr-event-gate__question mb-2">આજે Pregnancy Related Activity?</p>
        <div class="dr-preg-activities mb-3" id="pregnancyActivityPicker">
            @foreach([
                'heat' => 'Heat',
                'ai' => 'AI',
                'pregcheck' => 'Pregnancy Check',
                'delivery' => 'Delivery',
            ] as $key => $label)
            <label class="dr-preg-activity">
                <input type="checkbox" name="pregnancy_activities[]" value="{{ $key }}" class="pregnancy-activity-cb" data-activity="{{ $key }}"
                    {{ in_array($key, $pregActivities, true) ? 'checked' : '' }}>
                <span>{{ $label }}</span>
            </label>
            @endforeach
        </div>

        <div id="pregnancyPanelsWrap" hidden>
            {{-- Heat --}}
            <div class="dr-preg-panel" id="pregnancyPanelHeat" data-activity="heat" hidden>
                <h6 class="dr-preg-panel__title">🔥 Heat Entry</h6>
                <div class="dr-section-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-success add-preg-row" data-target="heatBody" data-template="heatRowTemplate">
                        <i class="fa-solid fa-circle-plus"></i> પંક્તિ ઉમેરો
                    </button>
                </div>
                <div class="dr-section-table-area">
                    <table class="table dr-section-table mb-0">
                        <thead><tr><th>પશુ</th><th>હીટ તારીખ</th><th>નોંધ</th><th></th></tr></thead>
                        <tbody id="heatBody"></tbody>
                    </table>
                </div>
            </div>

            {{-- AI --}}
            <div class="dr-preg-panel" id="pregnancyPanelAi" data-activity="ai" hidden>
                <h6 class="dr-preg-panel__title">💉 AI Entry</h6>
                <div class="dr-section-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-success add-preg-row" data-target="aiBody" data-template="aiRowTemplate">
                        <i class="fa-solid fa-circle-plus"></i> પંક્તિ ઉમેરો
                    </button>
                </div>
                <div class="dr-section-table-area">
                    <table class="table dr-section-table mb-0">
                        <thead><tr><th>પશુ</th><th>AI તારીખ</th><th>નોંધ</th><th></th></tr></thead>
                        <tbody id="aiBody"></tbody>
                    </table>
                </div>
            </div>

            {{-- Pregnancy Check --}}
            <div class="dr-preg-panel" id="pregnancyPanelPregcheck" data-activity="pregcheck" hidden>
                <h6 class="dr-preg-panel__title">🩺 Pregnancy Check</h6>
                <div class="dr-section-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-success add-preg-row" data-target="pregcheckBody" data-template="pregcheckRowTemplate">
                        <i class="fa-solid fa-circle-plus"></i> પંક્તિ ઉમેરો
                    </button>
                </div>
                <div class="dr-section-table-area">
                    <table class="table dr-section-table mb-0">
                        <thead><tr><th>પશુ</th><th>તપાસ તારીખ</th><th>નોંધ</th><th></th></tr></thead>
                        <tbody id="pregcheckBody">
                            @if(isset($report) && $report->pregnancy->count())
                                @foreach($report->pregnancy as $index => $preg)
                                <tr class="dr-dynamic-row">
                                    <td data-label="પશુ"><x-animal-select name="pregcheck_buffalo_id[]" :value="$preg->buffalo_id" /></td>
                                    <td data-label="તપાસ તારીખ"><input type="date" name="pregcheck_date[]" class="form-control" value="{{ $preg->checkup_date?->format('Y-m-d') ?? $reportDate }}"></td>
                                    <td data-label="નોંધ"><input type="text" name="pregcheck_note[]" class="form-control" value="{{ $preg->remarks ?? '' }}" placeholder="નોંધ"></td>
                                    <td class="dr-row-remove"><button type="button" class="dr-remove-btn remove-preg-row"{{ $index === 0 ? ' style="display:none;"' : '' }}><i class="fa-solid fa-circle-minus text-danger"></i></button></td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Delivery --}}
            <div class="dr-preg-panel" id="pregnancyPanelDelivery" data-activity="delivery" hidden>
                <h6 class="dr-preg-panel__title">🐄 Delivery</h6>
                <div class="dr-section-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-success add-preg-row" data-target="deliveryBody" data-template="deliveryRowTemplate">
                        <i class="fa-solid fa-circle-plus"></i> પંક્તિ ઉમેરો
                    </button>
                </div>
                <div class="dr-section-table-area">
                    <table class="table dr-section-table mb-0">
                        <thead><tr><th>પશુ</th><th>પ્રસૂતિ તારીખ</th><th>નોંધ</th><th></th></tr></thead>
                        <tbody id="deliveryBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <template id="heatRowTemplate">
            <tr class="dr-dynamic-row">
                <td data-label="પશુ"><x-animal-select name="heat_buffalo_id[]" /></td>
                <td data-label="હીટ તારીખ"><input type="date" name="heat_date[]" class="form-control" value="{{ $reportDate }}"></td>
                <td data-label="નોંધ"><input type="text" name="heat_note[]" class="form-control" placeholder="નોંધ"></td>
                <td class="dr-row-remove"><button type="button" class="dr-remove-btn remove-preg-row"><i class="fa-solid fa-circle-minus text-danger"></i></button></td>
            </tr>
        </template>
        <template id="aiRowTemplate">
            <tr class="dr-dynamic-row">
                <td data-label="પશુ"><x-animal-select name="ai_buffalo_id[]" /></td>
                <td data-label="AI તારીખ"><input type="date" name="ai_date[]" class="form-control" value="{{ $reportDate }}"></td>
                <td data-label="નોંધ"><input type="text" name="ai_note[]" class="form-control" placeholder="નોંધ"></td>
                <td class="dr-row-remove"><button type="button" class="dr-remove-btn remove-preg-row"><i class="fa-solid fa-circle-minus text-danger"></i></button></td>
            </tr>
        </template>
        <template id="pregcheckRowTemplate">
            <tr class="dr-dynamic-row">
                <td data-label="પશુ"><x-animal-select name="pregcheck_buffalo_id[]" /></td>
                <td data-label="તપાસ તારીખ"><input type="date" name="pregcheck_date[]" class="form-control" value="{{ $reportDate }}"></td>
                <td data-label="નોંધ"><input type="text" name="pregcheck_note[]" class="form-control" placeholder="નોંધ"></td>
                <td class="dr-row-remove"><button type="button" class="dr-remove-btn remove-preg-row"><i class="fa-solid fa-circle-minus text-danger"></i></button></td>
            </tr>
        </template>
        <template id="deliveryRowTemplate">
            <tr class="dr-dynamic-row">
                <td data-label="પશુ"><x-animal-select name="delivery_buffalo_id[]" /></td>
                <td data-label="પ્રસૂતિ તારીખ"><input type="date" name="delivery_date[]" class="form-control" value="{{ $reportDate }}"></td>
                <td data-label="નોંધ"><input type="text" name="delivery_note[]" class="form-control" placeholder="નોંધ"></td>
                <td class="dr-row-remove"><button type="button" class="dr-remove-btn remove-preg-row"><i class="fa-solid fa-circle-minus text-danger"></i></button></td>
            </tr>
        </template>
    </div>
</div>
