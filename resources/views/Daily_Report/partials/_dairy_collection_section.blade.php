<div class="card shadow-sm border-0 dr-section-card dr-collapsible-section is-collapsed dr-step-section" id="dairyCollectionSection" data-dr-step="9">
    <div class="card-header dr-collapsible-header p-0 dr-section-header" role="button" tabindex="0" aria-expanded="false">
        <div class="dr-collapsible-header-inner">
            <span class="dr-collapsible-title">🏭 ડેરીમાં આપેલું દૂધ</span>
            <i class="fa-solid fa-chevron-down dr-section-toggle" aria-hidden="true"></i>
        </div>
    </div>
    <div class="card-body dr-collapsible-content dr-section-content" id="dairyCollectionContent">
        <p class="text-muted small mb-3">લીટર ઑટો ગણાશે: ઉત્પાદન − ગ્રાહક વિતરણ. ફક્ત ફેટ, SNF, રકમ અને સ્લિપ દાખલ કરો.</p>

        <div class="ds-stats-grid ds-stats-grid-2 mb-3">
            <div class="erp-panel p-2">
                <div class="text-muted small">🐃 {{ __('milk_flow.buffalo') }} {{ __('milk_flow.remaining') }}</div>
                <div class="h5 mb-0"><span id="dairyBuffaloLiterDisplay">0.00</span> L</div>
            </div>
            <div class="erp-panel p-2">
                <div class="text-muted small">🐄 {{ __('milk_flow.cow') }} {{ __('milk_flow.remaining') }}</div>
                <div class="h5 mb-0"><span id="dairyCowLiterDisplay">0.00</span> L</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('milk_flow.slip_number') }}</label>
            <input type="text"
                name="dairy_slip_number"
                id="dairySlipNumber"
                class="form-control"
                value="{{ old('dairy_slip_number', $dairyCollection->slip_number ?? ($dairySlipNumber ?? '')) }}"
                readonly>
        </div>

        <h4 class="h6 mt-3">🐃 {{ __('milk_flow.buffalo') }}</h4>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">{{ __('milk_flow.fat') }} %</label>
                <input type="number" step="0.01" min="0" name="dairy_buffalo_fat" class="form-control" value="{{ old('dairy_buffalo_fat', $dairyCollection->buffalo_fat ?? '') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('milk_flow.snf') }} %</label>
                <input type="number" step="0.01" min="0" name="dairy_buffalo_snf" class="form-control" value="{{ old('dairy_buffalo_snf', $dairyCollection->buffalo_snf ?? '') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('milk_flow.amount') }}</label>
                <input type="number" step="0.01" min="0" name="dairy_buffalo_amount" class="form-control" value="{{ old('dairy_buffalo_amount', $dairyCollection->buffalo_amount ?? '') }}">
            </div>
        </div>

        <h4 class="h6 mt-3">🐄 {{ __('milk_flow.cow') }}</h4>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">{{ __('milk_flow.fat') }} %</label>
                <input type="number" step="0.01" min="0" name="dairy_cow_fat" class="form-control" value="{{ old('dairy_cow_fat', $dairyCollection->cow_fat ?? '') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('milk_flow.snf') }} %</label>
                <input type="number" step="0.01" min="0" name="dairy_cow_snf" class="form-control" value="{{ old('dairy_cow_snf', $dairyCollection->cow_snf ?? '') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('milk_flow.amount') }}</label>
                <input type="number" step="0.01" min="0" name="dairy_cow_amount" class="form-control" value="{{ old('dairy_cow_amount', $dairyCollection->cow_amount ?? '') }}">
            </div>
        </div>

        <div class="grid-2 mt-2">
            <div class="form-group">
                <label class="form-label">{{ __('milk_flow.slip_upload') }}</label>
                <input type="file" name="dairy_slip_image" class="form-control" accept="image/*">
                @if(!empty($dairyCollection?->slip_image))
                    <small class="text-muted"><a href="{{ asset('storage/' . $dairyCollection->slip_image) }}" target="_blank">{{ __('milk_flow.view_slip') }}</a></small>
                @endif
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('milk_flow.notes') }}</label>
                <input type="text" name="dairy_notes" class="form-control" value="{{ old('dairy_notes', $dairyCollection->notes ?? '') }}">
            </div>
        </div>

        <div class="alert alert-warning mt-3" id="dairyReconWarning" hidden>
            ⚠️ {{ __('milk_flow.reconciliation_error') }}
        </div>
    </div>
</div>
