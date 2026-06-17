@php
    $feedAnimals = ($feedAnimals ?? $buffaloes->where('status', 'active'))->sortBy('tag_number');
    $feedRecords = $feedRecords ?? collect();
    $oldGrid = old('feed_grid', []);
    $feedsJson = $feeds->map(fn ($f) => ['id' => $f->id, 'name' => $f->name, 'stock' => $f->available_quantity ?? 0])->values();
    $feedAnimalsJson = $feedAnimals->map(function ($buffalo) use ($feedRecords, $oldGrid, $feeds) {
        $record = $feedRecords->get($buffalo->id);
        $rowOld = $oldGrid[$buffalo->id] ?? [];
        $values = ['morning' => [], 'evening' => []];
        foreach ($feeds as $feed) {
            $values['morning'][$feed->id] = $rowOld['morning'][$feed->id] ?? ($record ? $record->qtyFor('morning', $feed->id) : '');
            $values['evening'][$feed->id] = $rowOld['evening'][$feed->id] ?? ($record ? $record->qtyFor('evening', $feed->id) : '');
        }
        return [
            'id' => $buffalo->id,
            'tag' => $buffalo->tag_number,
            'name' => $buffalo->name ?? '',
            'animal_type' => \App\Models\Buffalo::normalizeAnimalType($buffalo->animal_type ?? 'buffalo'),
            'values' => $values,
        ];
    })->values();
    $feedColCount = max($feeds->count(), 1);
    $feedSubColPct = number_format(28 / $feedColCount, 4, '.', '');
@endphp

<div id="feedGridHiddenStore" class="dr-grid-hidden-store" aria-hidden="true">
    @foreach($feedAnimals as $buffalo)
    @php
        $record = $feedRecords->get($buffalo->id);
        $rowOld = $oldGrid[$buffalo->id] ?? [];
    @endphp
    @foreach($feeds as $feed)
    <input type="hidden" class="feed-qty-store" name="feed_grid[{{ $buffalo->id }}][morning][{{ $feed->id }}]" data-sync-key="feed-{{ $buffalo->id }}-morning-{{ $feed->id }}" data-period="morning" data-feed-id="{{ $feed->id }}" data-buffalo-id="{{ $buffalo->id }}" value="{{ $rowOld['morning'][$feed->id] ?? ($record ? $record->qtyFor('morning', $feed->id) : '') }}">
    <input type="hidden" class="feed-qty-store" name="feed_grid[{{ $buffalo->id }}][evening][{{ $feed->id }}]" data-sync-key="feed-{{ $buffalo->id }}-evening-{{ $feed->id }}" data-period="evening" data-feed-id="{{ $feed->id }}" data-buffalo-id="{{ $buffalo->id }}" value="{{ $rowOld['evening'][$feed->id] ?? ($record ? $record->qtyFor('evening', $feed->id) : '') }}">
    @endforeach
    @endforeach
</div>
<script type="application/json" id="feedAnimalsJson">@json($feedAnimalsJson)</script>
<script type="application/json" id="feedTypesJson">@json($feedsJson)</script>

<div class="dr-grid-toolbar feed-toolbar">
    <div id="feedAnimalTabs" class="dr-animal-tabs" role="tablist" aria-label="Feed animal type"></div>
    <div class="dr-grid-toolbar__search">
        <input type="search" id="feedAnimalSearch" class="form-control form-control-sm" placeholder="ટેગ / નામ શોધો..." autocomplete="off">
    </div>
    <span class="dr-grid-toolbar__meta text-muted" id="feedGridCounts">{{ $feedAnimals->count() }} પશુ</span>
</div>

<div class="table-responsive feed-grid-wrap">
    <table class="table table-bordered feed-grid-table feed-table" id="feedGridTable">
        <colgroup>
            <col style="width:12%">
            <col style="width:18%">
            @foreach($feeds as $feed)
            <col style="width:{{ $feedSubColPct }}%">
            @endforeach
            @foreach($feeds as $feed)
            <col style="width:{{ $feedSubColPct }}%">
            @endforeach
            <col style="width:14%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">ટેગ નં.</th>
                <th rowspan="2">પશુ નામ</th>
                <th colspan="{{ $feeds->count() }}" class="feed-period-group">🌅 સવાર (Morning)</th>
                <th colspan="{{ $feeds->count() }}" class="feed-period-group feed-period-evening">🌇 સાંજ (Evening)</th>
                <th rowspan="2">કુલ ચારો</th>
            </tr>
            <tr>
                @foreach($feeds as $feed)
                <th class="feed-col-morning" data-feed-id="{{ $feed->id }}" data-stock="{{ $feed->available_quantity ?? 0 }}" title="સ્ટોક: {{ number_format($feed->available_quantity ?? 0, 2) }}">
                    {{ $feed->name }}
                </th>
                @endforeach
                @foreach($feeds as $feed)
                <th class="feed-col-evening feed-period-evening" data-feed-id="{{ $feed->id }}" data-stock="{{ $feed->available_quantity ?? 0 }}">
                    {{ $feed->name }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody id="feedGridBody"></tbody>
        <tfoot>
            <tr class="feed-summary-row">
                <td colspan="2">કુલ સારાંશ</td>
                @foreach($feeds as $feed)
                <td><span class="summary-morning" data-feed-id="{{ $feed->id }}">0</span></td>
                @endforeach
                @foreach($feeds as $feed)
                <td><span class="summary-evening" data-feed-id="{{ $feed->id }}">0</span></td>
                @endforeach
                <td><span id="summaryGrandTotal">0</span></td>
            </tr>
        </tfoot>
    </table>
</div>

<div id="feedGridPagination" class="dr-grid-pagination"></div>

<div class="feed-summary-bar">
    <span>🌅 <strong>સવાર કુલ:</strong> <span id="summaryTotalMorning">0</span></span>
    <span>🌇 <strong>સાંજ કુલ:</strong> <span id="summaryTotalEvening">0</span></span>
    <span>🌾 <strong>કુલ ચારો:</strong> <span id="summaryTotalFeed">0</span></span>
</div>

@foreach($feeds as $feed)
<input type="hidden" class="feed-stock-meta" data-feed-id="{{ $feed->id }}" data-stock="{{ $feed->available_quantity ?? 0 }}" data-label="{{ $feed->name }}">
@endforeach
