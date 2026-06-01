@extends('layouts.app')
@section('title','ખર્ચ')

@section('content')
<div class="page-header"><h2>💸 ખર્ચ</h2></div>

{{-- Add form --}}
<div class="card" style="margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:600; margin-bottom:16px;">નવો ખર્ચ ઉમેરો</h3>
    <form method="POST" action="{{ route('kharch.store') }}">
        @csrf
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">તારીખ *</label>
                <input type="date" name="expense_date" class="form-control" value="{{ today()->toDateString() }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">પ્રકાર *</label>
                <select name="category" class="form-control" required>
                    <option value="feed">🌾 ચારો / ઘાસ</option>
                    <option value="medicine">💊 દવા</option>
                    <option value="labour">👷 મજૂરી</option>
                    <option value="equipment">🔧 સાધન</option>
                    <option value="veterinary">🏥 પશુ ડૉક્ટર</option>
                    <option value="other">📌 અન્ય</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">ખર્ચ (₹) *</label>
                <input type="number" name="amount" step="0.01" min="0" class="form-control" placeholder="0.00" required>
            </div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">વર્ણન *</label>
                <input type="text" name="description" class="form-control" placeholder="દા.ત. ૨૦ ગઠ્ઠા ઘઉં" required>
            </div>
            <div class="form-group">
                <label class="form-label">ભેંસ (ઐચ્છિક)</label>
                <select name="buffalo_id" class="form-control">
                    <option value="">— બધા માટે —</option>
                    @foreach($buffaloes as $b)
                    <option value="{{ $b->id }}">{{ $b->tag_number }}{{ $b->name ? ' — '.$b->name : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">➕ ઉમેરો</button>
    </form>
</div>

{{-- Filter --}}
<form method="GET" style="display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap;">
    <select name="month" class="form-control" style="width:130px;" onchange="this.form.submit()">
        @foreach(range(1,12) as $m)
        <option value="{{ $m }}" {{ $m==$month ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->locale('gu')->translatedFormat('F') }}
        </option>
        @endforeach
    </select>
    <select name="year" class="form-control" style="width:100px;" onchange="this.form.submit()">
        @foreach(range(now()->year, 2020) as $y)
        <option value="{{ $y }}" {{ $y==$year ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
    </select>
</form>

{{-- Summary --}}
<div class="summary-row">
    <span>📅 {{ \Carbon\Carbon::create()->month($month)->locale('gu')->translatedFormat('F') }} {{ $year }}</span>
    <span>કુલ ખર્ચ: <strong>₹{{ number_format($total,0) }}</strong></span>
    @foreach($byCategory as $cat)
    <span>{{ match($cat->category){'feed'=>'🌾 ચારો','medicine'=>'💊 દવા','labour'=>'👷 મજૂરી','equipment'=>'🔧 સાધન','veterinary'=>'🏥 ડૉક્ટર',default=>'📌 અન્ય'} }}: <strong>₹{{ number_format($cat->total,0) }}</strong></span>
    @endforeach
</div>

{{-- Table --}}
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>તારીખ</th><th>પ્રકાર</th><th>વર્ણન</th><th>ભેંસ</th><th>ખર્ચ (₹)</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($expenses as $e)
                <tr>
                    <td>{{ $e->expense_date->format('d/m/Y') }}</td>
                    <td><span class="badge badge-blue">{{ $e->category_label }}</span></td>
                    <td>{{ $e->description }}</td>
                    <td>{{ $e->buffalo?->tag_number ?? '—' }}</td>
                    <td><strong>₹{{ number_format($e->amount,0) }}</strong></td>
                    <td>
                        <form method="POST" action="{{ route('kharch.destroy',$e) }}" onsubmit="return confirm('ડિલીટ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; color:#9ca3af; padding:30px;">કોઈ ખર્ચ નહીં</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $expenses->links() }}</div>
</div>
@endsection