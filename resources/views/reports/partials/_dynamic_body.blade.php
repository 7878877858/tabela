@php $type = $filters['report_type'] ?? 'milk'; @endphp

@if(in_array($type, ['milk', 'monthly', 'yearly', 'combined']) && isset($data['daily']))
<div class="card mb-3">
    <div class="card-header"><strong>Daily Milk Summary</strong></div>
    <div class="table-wrap">
        <table class="table table-bordered table-sm">
            <thead><tr><th>Date</th><th>Morning (L)</th><th>Evening (L)</th><th>Total (L)</th></tr></thead>
            <tbody>
                @forelse($data['daily'] as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ number_format($row['morning'], 2) }}</td>
                    <td>{{ number_format($row['evening'], 2) }}</td>
                    <td><strong>{{ number_format($row['total'], 2) }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">No data</td></tr>
                @endforelse
            </tbody>
            @if(isset($data['summary']))
            <tfoot>
                <tr style="font-weight:700;">
                    <td>Total</td>
                    <td>{{ number_format($data['summary']['morning'] ?? 0, 2) }}</td>
                    <td>{{ number_format($data['summary']['evening'] ?? 0, 2) }}</td>
                    <td>{{ number_format($data['summary']['total'] ?? 0, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif

@if(isset($data['byAnimal']))
<div class="card mb-3">
    <div class="card-header"><strong>Animal-wise Milk</strong></div>
    <div class="table-wrap">
        <table class="table table-bordered table-sm">
            <thead><tr><th>Tag</th><th>Name</th><th>Morning</th><th>Evening</th><th>Total</th><th>Days</th></tr></thead>
            <tbody>
                @foreach($data['byAnimal'] as $row)
                <tr>
                    <td>{{ $row['tag'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ number_format($row['morning'], 2) }}</td>
                    <td>{{ number_format($row['evening'], 2) }}</td>
                    <td><strong>{{ number_format($row['total'], 2) }}</strong></td>
                    <td>{{ $row['days'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(isset($data['byFeed']))
<div class="card mb-3">
    <div class="card-header"><strong>Feed Summary</strong></div>
    <div class="table-wrap">
        <table class="table table-bordered table-sm">
            <thead><tr><th>Feed</th><th>Morning</th><th>Evening</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($data['byFeed'] as $row)
                <tr>
                    <td>{{ $row['feed'] }}</td>
                    <td>{{ number_format($row['morning'], 2) }}</td>
                    <td>{{ number_format($row['evening'], 2) }}</td>
                    <td><strong>{{ number_format($row['total'], 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(isset($data['entries']) && $data['entries'] instanceof \Illuminate\Support\Collection && $type === 'expense')
<div class="card mb-3">
    <div class="card-header"><strong>Expenses</strong></div>
    <div class="table-wrap">
        <table class="table table-bordered table-sm">
            <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th></tr></thead>
            <tbody>
                @foreach($data['entries'] as $e)
                <tr>
                    <td>{{ $e->expense_date->format('d/m/Y') }}</td>
                    <td>{{ $e->category }}</td>
                    <td>{{ $e->description }}</td>
                    <td>{{ number_format($e->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(isset($data['entries']) && $type === 'income')
<div class="card mb-3">
    <div class="card-header"><strong>Income</strong></div>
    <div class="table-wrap">
        <table class="table table-bordered table-sm">
            <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th></tr></thead>
            <tbody>
                @foreach($data['entries'] as $e)
                <tr>
                    <td>{{ $e->income_date->format('d/m/Y') }}</td>
                    <td>{{ $e->category }}</td>
                    <td>{{ $e->description }}</td>
                    <td>{{ number_format($e->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(isset($data['entries']) && in_array($type, ['health', 'vaccination']))
<div class="card mb-3">
    <div class="card-header"><strong>{{ $data['title'] }}</strong></div>
    <div class="table-wrap">
        <table class="table table-bordered table-sm">
            @if($type === 'health')
            <thead><tr><th>Date</th><th>Animal</th><th>Issue</th><th>Treatment</th><th>Cost</th></tr></thead>
            <tbody>
                @foreach($data['entries'] as $e)
                <tr>
                    <td>{{ $e->record_date->format('d/m/Y') }}</td>
                    <td>{{ $e->buffalo?->display_label }}</td>
                    <td>{{ $e->health_issue }}</td>
                    <td>{{ $e->treatment }}</td>
                    <td>{{ number_format($e->medicine_cost, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            @else
            <thead><tr><th>Date</th><th>Animal</th><th>Vaccine</th><th>Remarks</th></tr></thead>
            <tbody>
                @foreach($data['entries'] as $e)
                <tr>
                    <td>{{ $e->vaccination_date->format('d/m/Y') }}</td>
                    <td>{{ $e->buffalo?->display_label }}</td>
                    <td>{{ $e->vaccine_name }}</td>
                    <td>{{ $e->remarks }}</td>
                </tr>
                @endforeach
            </tbody>
            @endif
        </table>
    </div>
</div>
@endif

@if(($type === 'combined' || $type === 'monthly') && isset($data['milk']))
@include('reports.partials._dynamic_body', ['data' => $data['milk'], 'filters' => array_merge($filters, ['report_type' => 'milk'])])
@include('reports.partials._dynamic_body', ['data' => $data['feed'] ?? [], 'filters' => array_merge($filters, ['report_type' => 'feed'])])
@endif

@if(isset($data['months']))
<div class="card mb-3">
    <div class="card-header"><strong>Yearly Breakdown</strong></div>
    <div class="table-wrap">
        <table class="table table-bordered table-sm">
            <thead><tr><th>Month</th><th>Milk (L)</th><th>Feed</th><th>Expense</th><th>Income</th></tr></thead>
            <tbody>
                @foreach($data['months'] as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::create()->month($row['month'])->format('F') }}</td>
                    <td>{{ number_format($row['milk'], 2) }}</td>
                    <td>{{ number_format($row['feed'], 2) }}</td>
                    <td>{{ number_format($row['expense'], 2) }}</td>
                    <td>{{ number_format($row['income'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
