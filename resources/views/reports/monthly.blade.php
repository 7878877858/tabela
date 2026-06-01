@extends('layouts.app')
@section('title','માસિક અહેવાલ')

@section('content')
<div class="page-header">
    <h2>📊 માસિક અહેવાલ</h2>
    <form method="GET" style="display:flex; gap:8px;">
        <select name="month" class="form-control" style="width:130px;" onchange="this.form.submit()">
            @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ $m==$month ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
            </option>
            @endforeach
        </select>
        <select name="year" class="form-control" style="width:100px;" onchange="this.form.submit()">
            @foreach(range(now()->year, 2020) as $y)
            <option value="{{ $y }}" {{ $y==$year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </form>
</div>

{{-- Summary cards --}}
<div class="grid-4" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="label">🥛 કુલ દૂધ</div>
        <div class="value">{{ number_format($totalMilk,1) }} L</div>
    </div>
    <div class="stat-card">
        <div class="label">💰 કુલ આવક</div>
        <div class="value">₹{{ number_format($totalIncome,0) }}</div>
    </div>
    <div class="stat-card">
        <div class="label">💸 કુલ ખર્ચ</div>
        <div class="value" style="color:#ef4444;">₹{{ number_format($totalExpense,0) }}</div>
    </div>
    <div class="stat-card">
        <div class="label">📈 {{ $netProfit >= 0 ? 'નફો' : 'નુકસાન' }}</div>
        <div class="value" style="color:{{ $netProfit >= 0 ? 'var(--primary)' : '#ef4444' }}">
            ₹{{ number_format(abs($netProfit),0) }}
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:20px;">
    {{-- Daily milk bar --}}
    <div class="card">
        <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">📅 દૈનિક દૂધ (L)</h3>
        <canvas id="dailyChart" height="200"></canvas>
    </div>

    {{-- Expense breakdown --}}
    <div class="card">
        <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">💸 ખર્ચ પ્રકાર</h3>
        @if($expenseByCategory->count())
        <canvas id="expPieChart" height="200"></canvas>
        @else
        <p style="color:#9ca3af; text-align:center; padding:40px 0;">ખર્ચ નહીં</p>
        @endif
    </div>
</div>

{{-- Per-buffalo table --}}
<div class="card" style="margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">🐃 ભેંસ દીઠ ઉત્પાદન</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ટેગ</th><th>નામ</th><th>કુલ (L)</th><th>દિવસો</th><th>સ્ ∅ L/દિ</th></tr></thead>
            <tbody>
                @forelse($buffaloSummary as $b)
                <tr>
                    <td><strong>{{ $b['tag'] }}</strong></td>
                    <td>{{ $b['name'] }}</td>
                    <td><strong>{{ number_format($b['total'],1) }}</strong></td>
                    <td>{{ $b['days'] }}</td>
                    <td>{{ number_format($b['avg'],1) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; color:#9ca3af;">ડેટા નહીં</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Daily milk table --}}
<div class="card">
    <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">📋 દૈનિક સારાંશ</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>તારીખ</th><th>કુલ (L)</th></tr></thead>
            <tbody>
                @foreach($dailyMilk as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->entry_date)->format('d/m/Y (D)') }}</td>
                    <td><strong>{{ number_format($row->total,1) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();

new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($dailyMilk->map(fn($d) => \Carbon\Carbon::parse($d->entry_date)->format('d'))) !!},
        datasets: [{ label: 'L', data: {!! json_encode($dailyMilk->pluck('total')) !!}, backgroundColor: primary+'88', borderColor: primary, borderWidth: 2, borderRadius: 4 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

@if($expenseByCategory->count())
new Chart(document.getElementById('expPieChart'), {
    type: 'pie',
    data: {
        labels: {!! json_encode($expenseByCategory->map(fn($e) => match($e->category){'feed'=>'ચારો','medicine'=>'દવા','labour'=>'મજૂરી','equipment'=>'સાધન','veterinary'=>'ડૉક્ટર',default=>'અન્ય'})) !!},
        datasets: [{ data: {!! json_encode($expenseByCategory->pluck('total')) !!}, backgroundColor: ['#16a34a','#ef4444','#f59e0b','#3b82f6','#8b5cf6','#6b7280'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
@endif
</script>
@endpush