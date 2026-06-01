@extends('layouts.app')
@section('title','ડૅશબોર્ડ')

@section('content')

{{-- Stat cards --}}
<div class="grid-4" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="label">🐃 કુલ ભેંસો</div>
        <div class="value">{{ $totalBuffaloes }}</div>
        <div class="sub">{{ $lactatingCount }} દૂધ આપે છે</div>
    </div>
    <div class="stat-card">
        <div class="label">🥛 આજનું દૂધ</div>
        <div class="value">{{ number_format($todayMilk,1) }} L</div>
        <div class="sub">{{ now()->format('d/m/Y') }}</div>
    </div>
    <div class="stat-card">
        <div class="label">📅 આ મહિને દૂધ</div>
        <div class="value">{{ number_format($monthMilk,1) }} L</div>
        <div class="sub">{{ now()->format('F Y') }}</div>
    </div>
    <div class="stat-card">
        <div class="label" style="color:{{ $netProfit >= 0 ? '#16a34a' : '#dc2626' }}">
            {{ $netProfit >= 0 ? '📈 નફો' : '📉 નુકસાન' }}
        </div>
        <div class="value" style="color:{{ $netProfit >= 0 ? 'var(--primary)' : '#ef4444' }}">
            {{ $settings['currency'] }}{{ number_format(abs($netProfit),0) }}
        </div>
        <div class="sub">આ મહિને</div>
    </div>
</div>

<div class="grid-4" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="label">💰 આ મહિને આવક</div>
        <div class="value" style="font-size:22px;">{{ $settings['currency'] }}{{ number_format($monthIncome,0) }}</div>
    </div>
    <div class="stat-card">
        <div class="label">💸 આ મહિને ખર્ચ</div>
        <div class="value" style="font-size:22px; color:#ef4444;">{{ $settings['currency'] }}{{ number_format($monthExpense,0) }}</div>
    </div>
    <div class="stat-card">
        <div class="label">⏳ પગાર બાકી</div>
        <div class="value" style="font-size:22px; color:#f59e0b;">{{ $settings['currency'] }}{{ number_format($pendingSalary,0) }}</div>
    </div>
    <div class="stat-card">
        <div class="label">🥛 સરેરાશ / દિવસ</div>
        <div class="value" style="font-size:22px;">{{ number_format($monthMilk / max(now()->day,1),1) }} L</div>
    </div>
</div>

{{-- Charts row --}}
<div class="grid-2" style="margin-bottom:20px; overflow-y: scroll;">
    <div class="card">
        <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">છેલ્લા ૭ દિવસ — દૂધ (L)</h3>
        <canvas id="milkChart" height="180"></canvas>
    </div>
    <div class="card" style="min-width: 183px;">
        <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">ખર્ચ — પ્રકાર મુજબ</h3>
        <canvas id="expenseChart" height="180"></canvas>
    </div>
</div>

{{-- Top producers --}}
<div class="grid-2">
    <div class="card">
        <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">🏆 સૌથી વધુ દૂધ (આ મહિને)</h3>
        <table>
            <thead><tr><th>#</th><th>ભેંસ</th><th>કુલ L</th></tr></thead>
            <tbody>
                @foreach($topBuffaloes->values() as $i => $b)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td><strong>{{ $b['tag'] }}</strong> {{ $b['name'] !== $b['tag'] ? '— '.$b['name'] : '' }}</td>
                    <td><strong>{{ number_format($b['total'],1) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">⚡ ઝડપી ઍક્શન</h3>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <a href="{{ route('milk.index') }}" class="btn btn-primary">🥛 આજની દૂધ એન્ટ્રી</a>
            <a href="{{ route('kharch.index') }}" class="btn btn-outline">💸 ખર્ચ ઉમેરો</a>
            <a href="{{ route('sale.index') }}" class="btn btn-outline">💰 વેચાણ એન્ટ્રી</a>
            <a href="{{ route('reports.monthly') }}" class="btn btn-ghost">📊 આ મહિનો જુઓ</a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const primary = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();

// Milk chart
new Chart(document.getElementById('milkChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($last7->pluck('date')) !!},
        datasets: [{
            label: 'L',
            data: {!! json_encode($last7->pluck('liters')) !!},
            backgroundColor: primary + '88',
            borderColor: primary,
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Expense donut
const expLabels = {!! json_encode($expenseBreakdown->map(fn($e) => match($e->category){
    'feed'=>'ચારો','medicine'=>'દવા','labour'=>'મજૂરી','equipment'=>'સાધન','veterinary'=>'ડૉક્ટર',default=>'અન્ય'
})) !!};
const expData = {!! json_encode($expenseBreakdown->pluck('total')) !!};

if (expData.length > 0) {
    new Chart(document.getElementById('expenseChart'), {
        type: 'doughnut',
        data: {
            labels: expLabels,
            datasets: [{ data: expData, backgroundColor: ['#16a34a','#ef4444','#f59e0b','#3b82f6','#8b5cf6','#6b7280'], borderWidth: 0 }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } } }
    });
} else {
    document.getElementById('expenseChart').parentElement.innerHTML += '<p style="text-align:center;color:#9ca3af;font-size:13px;">આ મહિને કોઈ ખર્ચ નથી</p>';
    document.getElementById('expenseChart').style.display = 'none';
}
</script>
@endpush