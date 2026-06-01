@extends('layouts.app')
@section('title','સેટિંગ્સ')

@section('content')
<div class="page-header"><h2>⚙️ સેટિંગ્સ</h2></div>

<div class="card" style="max-width:600px;">
    <form method="POST" action="{{ route('settings.update') }}">
        @csrf @method('PUT')

        <div class="form-group">
            <label class="form-label">🏡 તબેલાનું નામ</label>
            <input type="text" name="farm_name" class="form-control"
                value="{{ $settings['farm_name'] ?? 'મારો તબેલો' }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">🎨 ડૅશબોર્ડ / સાઇડબારનો રંગ</label>
            <div style="display:flex; align-items:center; gap:12px;">
                <input type="color" name="primary_color" id="colorPicker"
                    value="{{ $settings['primary_color'] ?? '#16a34a' }}"
                    style="width:60px; height:40px; border:none; cursor:pointer; border-radius:8px;">
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    @foreach(['#16a34a','#2563eb','#dc2626','#9333ea','#ea580c','#0891b2','#be185d','#374151'] as $color)
                    <div onclick="document.getElementById('colorPicker').value='{{ $color }}'; updatePreview('{{ $color }}')"
                        style="width:32px; height:32px; border-radius:6px; background:{{ $color }}; cursor:pointer; border:2px solid transparent;"
                        title="{{ $color }}"></div>
                    @endforeach
                </div>
            </div>
            <p style="font-size:12px; color:#6b7280; margin-top:6px;">⚠️ સેવ કર્યા પછી પેઇજ રિફ્રેશ કરો — નવો રંગ લાગુ થશે.</p>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">🥛 ડિફૉલ્ટ દૂધ ભાવ (₹/L)</label>
                <input type="number" name="milk_price" step="0.5" min="0"
                    value="{{ $settings['milk_price'] ?? '55' }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">💱 ચલણ ચિહ્ન</label>
                <select name="currency" class="form-control">
                    <option value="₹" {{ ($settings['currency']??'₹')==='₹' ? 'selected' : '' }}>₹ (રૂપિયા)</option>
                    <option value="$" {{ ($settings['currency']??'')==='$' ? 'selected' : '' }}>$ (Dollar)</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">💾 સેટિંગ્સ સેવ</button>
    </form>
</div>

<div class="card" style="margin-top:20px; max-width:600px; background:#fef9c3; border-color:#fde68a;">
    <h3 style="font-size:14px; font-weight:600; margin-bottom:8px; color:#92400e;">ℹ️ ડિફૉલ્ટ લૉગિન</h3>
    <p style="font-size:13px; color:#78350f;">Email: <code>admin@tabela.com</code> | Password: <code>admin123</code></p>
    <p style="font-size:12px; color:#a16207; margin-top:4px;">⚠️ Laravel profile settings (Laravel Breeze) માં password બદલો.</p>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('colorPicker').addEventListener('input', (e) => updatePreview(e.target.value));
function updatePreview(color) {
    document.documentElement.style.setProperty('--primary', color);
}
</script>
@endpush