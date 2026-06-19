@php
    $milkDistributions = $milkDistributions ?? collect();
    $dairyCollection = $dairyCollection ?? null;
    $milkCustomers = $milkCustomers ?? $customers ?? collect();
    $distByCustomer = $milkDistributions->keyBy('customer_id');
    $milkCustomersJson = $milkCustomers->map(function ($customer) use ($distByCustomer) {
        $dist = $distByCustomer->get($customer->id);
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'mobile' => $customer->mobile ?? '',
            'milk_type' => $dist->milk_type ?? 'buffalo',
            'morning' => $dist->morning_liter ?? '',
            'evening' => $dist->evening_liter ?? '',
            'rate' => $dist->rate_per_liter ?? '',
        ];
    })->values();
@endphp

<div class="card shadow-sm border-0 dr-section-card dr-collapsible-section is-collapsed dr-step-section" id="milkDistributionSection" data-dr-step="8">
    <div class="card-header dr-collapsible-header p-0 dr-section-header" role="button" tabindex="0" aria-expanded="false">
        <div class="dr-collapsible-header-inner dr-dist-header-row">
            <span class="dr-collapsible-title">🥛 આજે ગ્રાહકોને આપેલું દૂધ</span>
            <div class="dr-dist-header-summary" aria-live="polite">
                <span class="dr-dist-badge dr-dist-badge--buffalo">
                    🟣 {{ __('milk_flow.buffalo') }}: <strong id="distBuffaloTotal">0.00</strong> L
                </span>
                <span class="dr-dist-badge dr-dist-badge--cow">
                    🔵 {{ __('milk_flow.cow') }}: <strong id="distCowTotal">0.00</strong> L
                </span>
            </div>
            <div class="dr-section-header-actions">
                <i class="fa-solid fa-chevron-down dr-section-toggle" aria-hidden="true"></i>
            </div>
        </div>
    </div>
    <div class="card-body dr-collapsible-content dr-section-content p-0" id="milkDistributionContent">
        @if($milkCustomers->isEmpty())
            <p class="p-3 text-muted mb-0">
                {{ __('milk_flow.no_customers') }}
                <a href="{{ route('milk-customers.index') }}" target="_blank">{{ __('milk_flow.add_customer') }}</a>
            </p>
        @else
        <div id="distHiddenStore" class="dr-grid-hidden-store" aria-hidden="true">
            @foreach($milkCustomers as $customer)
            @php
                $dist = $distByCustomer->get($customer->id);
                $morning = $dist->morning_liter ?? '';
                $evening = $dist->evening_liter ?? '';
                $rate = $dist->rate_per_liter ?? '';
                $milkType = $dist->milk_type ?? 'buffalo';
            @endphp
            <input type="hidden" name="dist_customer_id[]" value="{{ $customer->id }}" data-sync-key="dist-{{ $customer->id }}-customer">
            <input type="hidden" name="dist_milk_type[]" value="{{ $milkType }}" data-sync-key="dist-{{ $customer->id }}-type">
            <input type="hidden" name="dist_morning_liter[]" value="{{ $morning }}" data-sync-key="dist-{{ $customer->id }}-morning">
            <input type="hidden" name="dist_evening_liter[]" value="{{ $evening }}" data-sync-key="dist-{{ $customer->id }}-evening">
            <input type="hidden" name="dist_rate_per_liter[]" value="{{ $rate }}" data-sync-key="dist-{{ $customer->id }}-rate">
            @endforeach
        </div>
        <script type="application/json" id="milkCustomersJson">@json($milkCustomersJson)</script>
        <script type="application/json" id="milkDistUiJson">@json(['buffalo' => __('milk_flow.buffalo'), 'cow' => __('milk_flow.cow')])</script>

        <div class="dr-grid-toolbar dr-dist-toolbar">
            <div class="dr-grid-toolbar__search">
                <input type="search" id="distCustomerSearch" class="form-control form-control-sm" placeholder="ગ્રાહક નામ / મોબાઇલ શોધો..." autocomplete="off">
            </div>
            <span class="dr-grid-toolbar__meta text-muted" id="distRowCounts">{{ $milkCustomers->count() }} ગ્રાહક</span>
        </div>
        <div class="table-responsive milk-grid-wrap">
            <table class="table table-bordered table-hover milk-grid-table mb-0" id="distTable">
                <thead>
                    <tr>
                        <th class="dr-grid-sr-col">ક્રમ</th>
                        <th>{{ __('milk_flow.customer') }}</th>
                        <th>{{ __('milk_flow.mobile') }}</th>
                        <th>{{ __('milk_flow.milk_type') }}</th>
                        <th>{{ __('milk_flow.morning_liter') }}</th>
                        <th>{{ __('milk_flow.evening_liter') }}</th>
                        <th>{{ __('milk_flow.total_liter') }}</th>
                        <th>{{ __('milk_flow.rate_per_liter') }}</th>
                        <th>{{ __('milk_flow.amount') }}</th>
                    </tr>
                </thead>
                <tbody id="distBody"></tbody>
            </table>
        </div>
        <div id="distGridPagination" class="dr-grid-pagination"></div>
        <div class="milk-summary-bar dist-summary-bar">
            <span>🥛 <strong>કુલ વિતરણ:</strong> <span id="distSummaryTotalLiter">0.00</span> L</span>
            <span>💰 <strong>ગ્રાહક આવક:</strong> ₹<span id="distSummaryTotalAmount">0.00</span></span>
            <span>📊 <strong>આજનું ઉત્પાદન:</strong> <span id="distSummaryProduction">0.00</span> L</span>
            <span>🫙 <strong>બાકી દૂધ:</strong> <span id="distSummaryRemaining">0.00</span> L</span>
        </div>
        @endif
    </div>
</div>
