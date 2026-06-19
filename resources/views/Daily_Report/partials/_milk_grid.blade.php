@php
    $milkAnimals = ($milkAnimals ?? collect())->sortBy('tag_number');
    $milkRecords = $milkRecords ?? collect();
    $oldGrid = old('milk_grid', []);
    $milkAnimalsJson = $milkAnimals->map(function ($buffalo) use ($milkRecords, $oldGrid) {
        $record = $milkRecords->get($buffalo->id);
        $rowOld = $oldGrid[$buffalo->id] ?? [];
        return [
            'id' => $buffalo->id,
            'tag' => $buffalo->tag_number,
            'name' => $buffalo->name ?? '',
            'animal_type' => \App\Models\Buffalo::normalizeAnimalType($buffalo->animal_type ?? 'buffalo'),
            'lactation_status' => $buffalo->lactation_status ?? 'lactating',
            'morning' => $rowOld['morning'] ?? ($record->morning_milk ?? ''),
            'evening' => $rowOld['evening'] ?? ($record->evening_milk ?? ''),
        ];
    })->values();
@endphp

<div id="milkGridHiddenStore" class="dr-grid-hidden-store" aria-hidden="true">
    @foreach($milkAnimals as $buffalo)
    @php
        $record = $milkRecords->get($buffalo->id);
        $rowOld = $oldGrid[$buffalo->id] ?? [];
        $morningVal = $rowOld['morning'] ?? ($record->morning_milk ?? '');
        $eveningVal = $rowOld['evening'] ?? ($record->evening_milk ?? '');
    @endphp
    <input type="hidden" name="milk_grid[{{ $buffalo->id }}][morning]" data-sync-key="milk-{{ $buffalo->id }}-morning" data-period="morning" data-buffalo-id="{{ $buffalo->id }}" value="{{ $morningVal !== '' && $morningVal !== null ? $morningVal : '' }}">
    <input type="hidden" name="milk_grid[{{ $buffalo->id }}][evening]" data-sync-key="milk-{{ $buffalo->id }}-evening" data-period="evening" data-buffalo-id="{{ $buffalo->id }}" value="{{ $eveningVal !== '' && $eveningVal !== null ? $eveningVal : '' }}">
    @endforeach
</div>
<script type="application/json" id="milkAnimalsJson">@json($milkAnimalsJson)</script>

<div class="dr-grid-toolbar milk-toolbar">
    <div id="milkAnimalTabs" class="dr-animal-tabs" role="tablist" aria-label="Milk animal type"></div>
    <div class="dr-grid-toolbar__search">
        <input type="search" id="milkAnimalSearch" class="form-control form-control-sm" placeholder="ટેગ / નામ શોધો..." autocomplete="off">
    </div>
    <span class="dr-grid-toolbar__meta text-muted" id="milkGridCounts">{{ $milkAnimals->count() }} પશુ</span>
</div>

<div class="table-responsive milk-grid-wrap">
    <table class="table table-bordered table-hover milk-grid-table milk-table" id="milkGridTable">
        <colgroup>
            <col style="width:6%">
            <col style="width:14%">
            <col style="width:22%">
            <col style="width:18%">
            <col style="width:18%">
            <col style="width:18%">
        </colgroup>
        <thead>
            <tr>
                <th class="dr-grid-sr-col">ક્રમ</th>
                <th>ટેગ નં.</th>
                <th>પશુ નામ</th>
                <th>🌅 સવાર (L)</th>
                <th>🌇 સાંજ (L)</th>
                <th>કુલ (L)</th>
            </tr>
        </thead>
        <tbody id="milkGridBody"></tbody>
    </table>
</div>

<div id="milkGridPagination" class="dr-grid-pagination"></div>

@if($milkAnimals->isEmpty())
<div class="alert alert-info mb-0 mt-2" style="font-size:13px;">
    કોઈ સક્રિય પશુ મળ્યું નથી. પશુ માસ્ટરમાં નોંધણી કરો.
</div>
@endif

<div class="milk-summary-bar">
    <span>🌅 <strong>સવાર કુલ:</strong> <span id="milkSummaryMorning">0.00</span> L</span>
    <span>🌇 <strong>સાંજ કુલ:</strong> <span id="milkSummaryEvening">0.00</span> L</span>
    <span>🥛 <strong>કુલ દૂધ:</strong> <span id="milkSummaryGrand">0.00</span> L</span>
</div>

<div class="milk-sticky-summary" id="milkStickySummary">
    <div><strong>એન્ટ્રી:</strong> <span class="val" id="milkStickyAnimals">0</span></div>
    <div><strong>સવાર:</strong> <span class="val" id="milkStickyMorning">0.00</span> L</div>
    <div><strong>સાંજ:</strong> <span class="val" id="milkStickyEvening">0.00</span> L</div>
    <div><strong>કુલ:</strong> <span class="val" id="milkStickyTotal">0.00</span> L</div>
    <span class="milk-autosave-status local" id="milkAutosaveStatus" aria-live="polite">🟢 ઓફલાઇન ડ્રાફ્ટ સક્રિય</span>
</div>
