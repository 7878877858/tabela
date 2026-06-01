@extends('layouts.app')
@section('title','દૂધ એન્ટ્રી')

@section('content')
<div class="page-header">
    <h2>🥛 દૂધ એન્ટ્રી</h2>
    <a href="{{ route('milk.history') }}" class="btn btn-outline btn-sm">📋 ઇતિહાસ</a>
</div>

{{-- Date selector --}}
<div class="card" style="margin-bottom:16px; padding:14px 20px;">
    <form method="GET" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <label class="form-label" style="margin:0;">📅 તારીખ:</label>
        <input type="date" name="date" value="{{ $date }}" class="form-control" style="width:180px;" onchange="this.form.submit()">
        <span style="font-size:13px; color:#6b7280;">
            કુલ: <strong>સવાર {{ number_format($totalMorning,1) }}L + સાંજ {{ number_format($totalEvening,1) }}L = {{ number_format($totalLiters,1) }}L</strong>
        </span>
    </form>
</div>

<div class="card">
    <form method="POST" action="{{ route('milk.store') }}">
        @csrf
        <input type="hidden" name="entry_date" value="{{ $date }}">

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ટેગ / નામ</th>
                        <th>સવારનું (L)</th>
                        <th>સાંજનું (L)</th>
                        <th>કુલ (L)</th>
                        <th>નોંધ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($buffaloes as $i => $buffalo)
                    @php $entry = $entries[$buffalo->id] ?? null; @endphp
                    <tr>
                        <td>
                            <input type="hidden" name="entries[{{ $i }}][buffalo_id]" value="{{ $buffalo->id }}">
                            <strong>{{ $buffalo->tag_number }}</strong>
                            @if($buffalo->name) <span style="color:#6b7280; font-size:12px;">{{ $buffalo->name }}</span> @endif
                        </td>
                        <td>
                            <input type="number" step="0.1" min="0" name="entries[{{ $i }}][morning_liters]"
                                value="{{ $entry->morning_liters ?? '' }}"
                                class="form-control" style="width:100px;"
                                placeholder="0.0" onchange="calcTotal({{ $i }})">
                        </td>
                        <td>
                            <input type="number" step="0.1" min="0" name="entries[{{ $i }}][evening_liters]"
                                value="{{ $entry->evening_liters ?? '' }}"
                                class="form-control" style="width:100px;"
                                placeholder="0.0" onchange="calcTotal({{ $i }})">
                        </td>
                        <td id="total-{{ $i }}" style="font-weight:600; color:var(--primary);">
                            {{ $entry ? number_format($entry->total_liters,1) : '—' }}
                        </td>
                        <td>
                            <input type="text" name="entries[{{ $i }}][notes]"
                                value="{{ $entry->notes ?? '' }}"
                                class="form-control" style="width:140px;" placeholder="વૈકલ્પિક">
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center; color:#9ca3af; padding:30px;">
                        કોઈ સક્રિય ભેંસ નથી. પહેલા <a href="{{ route('buffalo.create') }}">ભેંસ ઉમેરો</a>.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($buffaloes->count() > 0)
        <div style="margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="font-size:14px; color:#6b7280;">
                કુલ ભેંસ: <strong>{{ $buffaloes->count() }}</strong>
            </div>
            <button type="submit" class="btn btn-primary">💾 સેવ કરો</button>
        </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
function calcTotal(i) {
    const m = parseFloat(document.querySelector(`[name="entries[${i}][morning_liters]"]`).value) || 0;
    const e = parseFloat(document.querySelector(`[name="entries[${i}][evening_liters]"]`).value) || 0;
    document.getElementById(`total-${i}`).textContent = (m + e).toFixed(1);
}
</script>
@endpush