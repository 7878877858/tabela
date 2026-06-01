@extends('layouts.app')
@section('title','દૂધ વેચાણ')

@section('content')
<div class="page-header"><h2>💰 દૂધ વેચાણ</h2></div>

<div class="card" style="margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">નવું વેચાણ</h3>
    <form method="POST" action="{{ route('sale.store') }}">
        @csrf
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">તારીખ *</label>
                <input type="date" name="sale_date" class="form-control" value="{{ today()->toDateString() }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">લિટર *</label>
                <input type="number" step="0.1" min="0.1" name="liters_sold" id="liters" class="form-control" placeholder="0.0" required oninput="calcSale()">
            </div>
            <div class="form-group">
                <label class="form-label">ભાવ / L (₹) *</label>
                <input type="number" step="0.01" name="price_per_liter" id="price" class="form-control"
                    value="{{ \App\Models\Setting::get('milk_price',55) }}" required oninput="calcSale()">
            </div>
        </div>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">ખરીદનારનું નામ</label>
                <input type="text" name="buyer_name" class="form-control" placeholder="ઐચ્છિક">
            </div>
            <div class="form-group">
                <label class="form-label">પેમેન્ટ</label>
                <select name="payment_status" class="form-control">
                    <option value="paid">✅ મળ્યું</option>
                    <option value="pending">⏳ બાકી</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">કુલ</label>
                <div id="sale-total" style="font-size:24px; font-weight:700; color:var(--primary); padding-top:4px;">₹0</div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">➕ ઉમેરો</button>
    </form>
</div>

{{-- Month filter --}}
<form method="GET" style="display:flex; gap:10px; margin-bottom:16px;">
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

<div class="summary-row">
    <span>📦 {{ number_format($totalLiters,1) }} L વેચ્યા</span>
    <span>💰 આવક: <strong>₹{{ number_format($totalIncome,0) }}</strong></span>
    @if($pending > 0)<span style="color:#dc2626;">⏳ બાકી: <strong>₹{{ number_format($pending,0) }}</strong></span>@endif
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>તારીખ</th><th>L</th><th>ભાવ/L</th><th>કુલ</th><th>ખરીદનાર</th><th>પેમેન્ટ</th><th></th></tr></thead>
            <tbody>
                @forelse($sales as $s)
                <tr>
                    <td>{{ $s->sale_date->format('d/m/Y') }}</td>
                    <td>{{ number_format($s->liters_sold,1) }}</td>
                    <td>₹{{ $s->price_per_liter }}</td>
                    <td><strong>₹{{ number_format($s->liters_sold * $s->price_per_liter,0) }}</strong></td>
                    <td>{{ $s->buyer_name ?? '—' }}</td>
                    <td>
                        @if($s->payment_status === 'paid')
                            <span class="badge badge-green">✅ મળ્યું</span>
                        @else
                            <form method="POST" action="{{ route('sale.pay',$s) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm badge-yellow" style="border:none; cursor:pointer; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:600; background:#fef9c3; color:#ca8a04;">⏳ બાકી — મળ્યું ✓</button>
                            </form>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('sale.destroy',$s) }}" onsubmit="return confirm('ડિલીટ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:#9ca3af; padding:30px;">કોઈ વેચાણ નહીં</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $sales->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
function calcSale() {
    const l = parseFloat(document.getElementById('liters').value) || 0;
    const p = parseFloat(document.getElementById('price').value) || 0;
    document.getElementById('sale-total').textContent = '₹' + Math.round(l * p).toLocaleString('en-IN');
}
calcSale();
</script>
@endpush