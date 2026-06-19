@php
    $manureIncomes = $manureIncomes ?? collect();
    $animalIncomes = $animalIncomes ?? collect();
    $otherIncomes = $otherIncomes ?? collect();
    $saleAnimals = $saleAnimals ?? collect();
    $currency = \App\Models\Setting::get('currency', '₹');
@endphp

<div class="card shadow-sm border-0 dr-section-card dr-collapsible-section is-collapsed dr-step-section" id="incomeSection" data-dr-step="10">
    <div class="card-header dr-collapsible-header p-0 dr-section-header" role="button" tabindex="0" aria-expanded="false">
        <div class="dr-collapsible-header-inner">
            <span class="dr-collapsible-title">💵 આવક</span>
            <i class="fa-solid fa-chevron-down dr-section-toggle" aria-hidden="true"></i>
        </div>
    </div>
    <div class="card-body dr-collapsible-content dr-section-content" id="incomeContent">
        <p class="text-muted small mb-3">{{ __('income.milk_auto_detail') }}</p>

        <div class="ds-stats-grid ds-stats-grid-2 mb-4">
            <div class="erp-panel p-3 dr-auto-income-card">
                <div class="text-muted small">🥛 {{ __('income.customer_milk_income') }}</div>
                <div class="h4 mb-0 text-primary">{{ $currency }}<span id="drAutoCustomerMilkIncome">0.00</span></div>
                <div class="small text-muted">{{ __('income.auto_readonly') }}</div>
            </div>
            <div class="erp-panel p-3 dr-auto-income-card">
                <div class="text-muted small">🏭 {{ __('income.dairy_income') }}</div>
                <div class="h4 mb-0 text-primary">{{ $currency }}<span id="drAutoDairyIncome">0.00</span></div>
                <div class="small text-muted">{{ __('income.auto_readonly') }}</div>
            </div>
        </div>

        <h4 class="h6 d-flex align-items-center justify-content-between">
            <span>💩 {{ __('income.manure_sale') }}</span>
            <button type="button" class="dr-section-add-btn btn btn-sm btn-ghost" id="addManureRow" title="પંક્તિ ઉમેરો">
                <i class="fa-solid fa-circle-plus"></i>
            </button>
        </h4>
        <div class="dr-section-table-area mb-4">
            <table class="table dr-section-table mb-0" id="manureTable">
                <thead>
                    <tr>
                        <th>{{ __('income.weight_kg') }}</th>
                        <th>{{ __('income.rate_per_kg') }}</th>
                        <th>{{ __('income.amount') }}</th>
                        <th>{{ __('income.buyer_name') }}</th>
                        <th>{{ __('income.remarks') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="manureBody">
                    @forelse($manureIncomes as $index => $row)
                    <tr class="dr-dynamic-row dr-manure-row">
                        <td data-label="{{ __('income.weight_kg') }}">
                            <input type="number" step="0.01" min="0" name="manure_weight_kg[]" class="form-control manure-weight" value="{{ $row->weight_kg }}">
                        </td>
                        <td data-label="{{ __('income.rate_per_kg') }}">
                            <input type="number" step="0.01" min="0" name="manure_rate_per_kg[]" class="form-control manure-rate" value="{{ $row->rate_per_kg }}">
                        </td>
                        <td class="manure-amount" data-label="{{ __('income.amount') }}">{{ number_format($row->amount, 2) }}</td>
                        <td data-label="{{ __('income.buyer_name') }}">
                            <input type="text" name="manure_buyer_name[]" class="form-control" value="{{ $row->buyer_name }}">
                        </td>
                        <td data-label="{{ __('income.remarks') }}">
                            <input type="text" name="manure_remarks[]" class="form-control" value="{{ $row->remarks }}">
                        </td>
                        <td>
                            <button type="button" class="dr-remove-btn remove-manure-row" @if($index === 0 && $manureIncomes->count() === 1) style="display:none" @endif>
                                <i class="fa-solid fa-circle-minus text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr class="dr-dynamic-row dr-manure-row">
                        <td><input type="number" step="0.01" min="0" name="manure_weight_kg[]" class="form-control manure-weight"></td>
                        <td><input type="number" step="0.01" min="0" name="manure_rate_per_kg[]" class="form-control manure-rate"></td>
                        <td class="manure-amount">0.00</td>
                        <td><input type="text" name="manure_buyer_name[]" class="form-control"></td>
                        <td><input type="text" name="manure_remarks[]" class="form-control"></td>
                        <td><button type="button" class="dr-remove-btn remove-manure-row" style="display:none"><i class="fa-solid fa-circle-minus text-danger"></i></button></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h4 class="h6 d-flex align-items-center justify-content-between">
            <span>🐃 {{ __('income.animal_sale') }}</span>
            <button type="button" class="dr-section-add-btn btn btn-sm btn-ghost" id="addAnimalSaleRow" title="પંક્તિ ઉમેરો">
                <i class="fa-solid fa-circle-plus"></i>
            </button>
        </h4>
        <div class="dr-grid-toolbar animal-sale-toolbar mb-2">
            <div id="animalSaleTypeTabs" class="dr-animal-tabs" role="tablist" aria-label="Animal sale type filter"></div>
            <div class="dr-grid-toolbar__search">
                <input type="search" id="animalSaleSearch" class="form-control form-control-sm" placeholder="ટેગ / નામ શોધો..." autocomplete="off">
            </div>
            <span class="dr-grid-toolbar__meta text-muted" id="animalSaleFilterMeta">{{ $saleAnimals->count() }} પશુ</span>
        </div>
        <div class="dr-section-table-area mb-4">
            <table class="table dr-section-table mb-0" id="animalSaleTable">
                <thead>
                    <tr>
                        <th>{{ __('income.animal') }}</th>
                        <th>{{ __('income.sale_price') }}</th>
                        <th>{{ __('income.buyer_name') }}</th>
                        <th>{{ __('income.remarks') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="animalSaleBody">
                    @forelse($animalIncomes as $index => $row)
                    <tr class="dr-dynamic-row dr-animal-sale-row">
                        <td class="animal-sale-select-cell" data-label="{{ __('income.animal') }}">
                            @include('Daily_Report.partials._animal_sale_select', [
                                'saleAnimals' => $saleAnimals,
                                'selected' => $row->buffalo_id,
                            ])
                        </td>
                        <td data-label="{{ __('income.sale_price') }}">
                            <input type="number" step="0.01" min="0" name="animal_sale_amount[]" class="form-control" value="{{ $row->amount }}">
                        </td>
                        <td data-label="{{ __('income.buyer_name') }}">
                            <input type="text" name="animal_sale_buyer_name[]" class="form-control" value="{{ $row->buyer_name }}">
                        </td>
                        <td data-label="{{ __('income.remarks') }}">
                            <input type="text" name="animal_sale_remarks[]" class="form-control" value="{{ $row->remarks }}">
                        </td>
                        <td>
                            <button type="button" class="dr-remove-btn remove-animal-sale-row" @if($index === 0 && $animalIncomes->count() === 1) style="display:none" @endif>
                                <i class="fa-solid fa-circle-minus text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr class="dr-dynamic-row dr-animal-sale-row">
                        <td class="animal-sale-select-cell">
                            @include('Daily_Report.partials._animal_sale_select', ['saleAnimals' => $saleAnimals])
                        </td>
                        <td><input type="number" step="0.01" min="0" name="animal_sale_amount[]" class="form-control"></td>
                        <td><input type="text" name="animal_sale_buyer_name[]" class="form-control"></td>
                        <td><input type="text" name="animal_sale_remarks[]" class="form-control"></td>
                        <td><button type="button" class="dr-remove-btn remove-animal-sale-row" style="display:none"><i class="fa-solid fa-circle-minus text-danger"></i></button></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h4 class="h6 d-flex align-items-center justify-content-between">
            <span>📦 {{ __('income.other_income') }}</span>
            <button type="button" class="dr-section-add-btn btn btn-sm btn-ghost" id="addOtherIncomeRow" title="પંક્તિ ઉમેરો">
                <i class="fa-solid fa-circle-plus"></i>
            </button>
        </h4>
        <div class="dr-section-table-area">
            <table class="table dr-section-table mb-0" id="otherIncomeTable">
                <thead>
                    <tr>
                        <th>{{ __('income.title') }}</th>
                        <th>{{ __('income.amount') }}</th>
                        <th>{{ __('income.remarks') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="otherIncomeBody">
                    @forelse($otherIncomes as $index => $row)
                    <tr class="dr-dynamic-row dr-other-income-row">
                        <td data-label="{{ __('income.title') }}">
                            <input type="text" name="other_income_title[]" class="form-control" value="{{ $row->description }}">
                        </td>
                        <td data-label="{{ __('income.amount') }}">
                            <input type="number" step="0.01" min="0" name="other_income_amount[]" class="form-control" value="{{ $row->amount }}">
                        </td>
                        <td data-label="{{ __('income.remarks') }}">
                            <input type="text" name="other_income_remarks[]" class="form-control" value="{{ $row->remarks }}">
                        </td>
                        <td>
                            <button type="button" class="dr-remove-btn remove-other-income-row" @if($index === 0 && $otherIncomes->count() === 1) style="display:none" @endif>
                                <i class="fa-solid fa-circle-minus text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr class="dr-dynamic-row dr-other-income-row">
                        <td><input type="text" name="other_income_title[]" class="form-control"></td>
                        <td><input type="number" step="0.01" min="0" name="other_income_amount[]" class="form-control"></td>
                        <td><input type="text" name="other_income_remarks[]" class="form-control"></td>
                        <td><button type="button" class="dr-remove-btn remove-other-income-row" style="display:none"><i class="fa-solid fa-circle-minus text-danger"></i></button></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
