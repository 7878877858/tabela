@extends('layouts.app')
@section('title','વાર્ષિક અહેવાલ')

@section('content')
<div class="page-header">
    <h2>📈 વાર્ષિક અહેવાલ</h2>
    <form method="GET">
        <select name="year" class="form-control" style="width:100px;" onchange="this.form.submit()">
            @foreach(range(now()->year, 2020) as $y)
            <option value="{{ $y }}" {{ $y==$year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid-4" style="margin-bottom:20px;">
    <div class="stat-card"><div class="label">🥛 {{ $year }} કુલ દૂધ</div><div class="value">{{ number_format($totalMilk,0) }} L</div></div>
    <div class="stat-card"><div class="label">💰 કુલ આવક</div><div class="value">₹{{ number_format($totalIncome,0) }}</div></div>
    <div class="stat-card"><div class="label">💸 કુલ ખર્ચ</div><div class="value" style="color:#ef4444;">₹{{ number_format($totalExpense,0) }}</div></div>
    <div class="stat-card">
        <div class="label">{{ $totalProfit >= 0 ? '📈 નફો' : '📉 નુકસાન' }}</div>
        <div class="value" style="color:{{ $totalProfit >= 0 ? 'var(--primary)' : '#ef4444' }}">₹{{ number_format(abs($totalProfit),0) }}</div>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">📊 માસ વાર ચાર્ટ</h3>
    <canvas id="yearChart" height="120"></canvas>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>મહિનો</th><th>દૂધ (L)</th><th>આવક (₹)</th><th>ખર્ચ (₹)</th><th>નફો / નુકસાન</th></tr></thead>
            <tbody>
                @foreach($monthly as $row)
                @php $months = ['','જાન','ફેબ','માર','એપ','મે','જૂન','જુલ','ઑગ','સપ','ઑક','નવ','ડિસ'] @endphp
                <tr>
                    <td><strong>{{ $months[$row['month']] }}</strong></td>
                    <td>{{ number_format($row['milk'],1) }}</td>
                    <td>₹{{ number_format($row['income'],0) }}</td>
                    <td>₹{{ number_format($row['expense'],0) }}</td>
                    <td style="color:{{ $row['profit'] >= 0 ? 'var(--primary)' : '#ef4444' }}; font-weight:600;">
                        {{ $row['profit'] >= 0 ? '+' : '' }}₹{{ number_format($row['profit'],0) }}
                    </td>
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
const labels = ['જાન','ફેબ','માર','એપ','મે','જૂન','જુલ','ઑગ','સપ','ઑક','નવ','ડિસ'];
const income  = {!! json_encode($monthly->pluck('income')) !!};
const expense = {!! json_encode($monthly->pluck('expense')) !!};

new Chart(document.getElementById('yearChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'આવક', data: income,  backgroundColor: primary+'bb', borderRadius: 4 },
            { label: 'ખર્ચ', data: expense, backgroundColor: '#ef444488', borderRadius: 4 }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: { legend: { position: 'top' } }
    }
});
</script>
@endpush