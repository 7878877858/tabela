@extends('layouts.app')
@section('title', __('sale.milk_sales'))

@section('content')
<div class="page-header"><h2>💰 {{ __('sale.milk_sales') }}</h2></div>

<div class="card" style="margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">{{ __('sale.new_sale') }}</h3>
    <form method="POST" action="{{ route('sale.store') }}">
        @csrf
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">{{ __('sale.date') }} *</label>
                <input type="date" name="sale_date" class="form-control" value="{{ today()->toDateString() }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('sale.liters') }} *</label>
                <input type="number" step="0.1" min="0.1" name="liters_sold" id="liters" class="form-control" placeholder="0.0" required oninput="calcSale()">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('sale.price_per_liter') }} (₹) *</label>
                <input type="number" step="0.01" name="price_per_liter" id="price" class="form-control"
                    value="{{ \App\Models\Setting::get('milk_price',55) }}" required oninput="calcSale()">
            </div>
        </div>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">{{ __('sale.buyer_name') }}</label>
                <input type="text" name="buyer_name" class="form-control" placeholder="{{ __('sale.optional') }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('sale.payment') }}</label>
                <select name="payment_status" class="form-control">
                    <option value="paid">✅ {{ __('sale.paid') }}</option>
                    <option value="pending">⏳ {{ __('sale.pending') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('sale.total') }}</label>
                <div id="sale-total" style="font-size:24px; font-weight:700; color:var(--primary); padding-top:4px;">₹0</div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">➕ {{ __('sale.add') }}</button>
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
    <span>📦 {{ number_format($totalLiters,1) }} L {{ __('sale.sold_liters') }}</span>
    <span>💰 {{ __('sale.income') }}: <strong>₹{{ number_format($totalIncome,0) }}</strong></span>
    @if($pending > 0)<span style="color:#dc2626;">⏳ {{ __('sale.pending') }}: <strong>₹{{ number_format($pending,0) }}</strong></span>@endif
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('sale.date') }}</th><th>{{ __('sale.liters') }}</th><th>{{ __('sale.price_per_liter') }}</th><th>{{ __('sale.total') }}</th><th>{{ __('sale.buyer_name') }}</th><th>{{ __('sale.payment') }}</th><th></th></tr></thead>
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
                            <span class="badge badge-green">✅ {{ __('sale.paid') }}</span>
                        @else
                            <form method="POST" action="{{ route('sale.pay',$s) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm badge-yellow" style="border:none; cursor:pointer; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:600; background:#fef9c3; color:#ca8a04;">⏳ {{ __('sale.pending') }} — {{ __('sale.mark_paid') }}</button>
                            </form>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('sale.destroy',$s) }}" onsubmit="return confirm('{{ __('sale.delete_confirm') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:#9ca3af; padding:30px;">{{ __('sale.no_sales') }}</td></tr>
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