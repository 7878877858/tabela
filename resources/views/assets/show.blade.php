@extends('layouts.app')
@section('title', $asset->name)

@section('content')
@php $currency = \App\Models\Setting::get('currency', '₹'); @endphp
<link rel="stylesheet" href="{{ asset('static/css/asset-management.css') }}">

<div class="am-page">
    <div class="am-hero">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-start gap-3 flex-wrap">
                @if($asset->image_url)
                <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}" class="am-asset-thumb" style="width:72px;height:72px;">
                @else
                <span class="am-asset-thumb am-asset-thumb--empty" style="width:72px;height:72px;font-size:28px;">🚜</span>
                @endif
                <div>
                    <h2>🚜 {{ $asset->name }}</h2>
                    <p>{{ $asset->asset_code }} · {{ $asset->category_label }} · <span class="badge badge-green">{{ $asset->status_label }}</span></p>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('assets.edit', $asset) }}" class="btn btn-light btn-sm">✏️ {{ __('asset.edit') }}</a>
                <a href="{{ route('assets.index') }}" class="btn btn-outline-light btn-sm">← {{ __('asset.back') }}</a>
            </div>
        </div>
    </div>

    <div class="am-stat-row">
        <div class="am-stat-box">
            <strong>{{ $currency }}{{ number_format($stats['purchase_price'], 0) }}</strong>
            <small>{{ __('asset.purchase_price') }}</small>
        </div>
        <div class="am-stat-box">
            <strong>{{ $currency }}{{ number_format($stats['current_value'], 0) }}</strong>
            <small>{{ __('asset.current_value') }}</small>
        </div>
        <div class="am-stat-box">
            <strong>{{ $currency }}{{ number_format($stats['total_maintenance'], 0) }}</strong>
            <small>{{ __('asset.total_maintenance') }}</small>
        </div>
        <div class="am-stat-box">
            <strong>{{ $currency }}{{ number_format($stats['total_repairs'], 0) }}</strong>
            <small>{{ __('asset.total_repairs') }}</small>
        </div>
        <div class="am-stat-box">
            <strong>{{ $stats['next_service_date'] ? \Carbon\Carbon::parse($stats['next_service_date'])->format('d/m/Y') : '—' }}</strong>
            <small>{{ __('asset.next_service') }}</small>
        </div>
    </div>

    <div class="am-card">
        <h3 class="am-card__title">ℹ️ {{ __('asset.asset_information') }}</h3>
        <div class="am-asset-photo-card">
            @if($asset->image_url)
            <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}" class="am-asset-photo">
            @else
            <div class="am-asset-photo--empty" aria-hidden="true">🚜</div>
            <p class="text-muted mt-2 mb-0" style="font-size:13px;">{{ __('asset.no_image') }}</p>
            @endif
        </div>
        <div class="am-detail-grid">
            <div class="am-detail-item"><label>{{ __('asset.purchase_date') }}</label><span>{{ $asset->purchase_date?->format('d/m/Y') ?? '—' }}</span></div>
            <div class="am-detail-item"><label>{{ __('asset.vendor_name') }}</label><span>{{ $asset->vendor_name ?? '—' }}</span></div>
            <div class="am-detail-item"><label>{{ __('asset.vendor_mobile') }}</label><span>{{ $asset->vendor_mobile ?? '—' }}</span></div>
            <div class="am-detail-item"><label>{{ __('asset.warranty_months') }}</label><span>{{ $asset->warranty_months ? $asset->warranty_months . ' ' . __('asset.months') : '—' }}</span></div>
            <div class="am-detail-item"><label>{{ __('asset.last_maintenance') }}</label><span>{{ $stats['last_maintenance_date']?->format('d/m/Y') ?? '—' }}</span></div>
            <div class="am-detail-item"><label>{{ __('asset.notes') }}</label><span>{{ $asset->notes ?? $asset->description ?? '—' }}</span></div>
        </div>
    </div>

    <div class="am-card">
        <h3 class="am-card__title">🔧 {{ __('asset.add_maintenance') }}</h3>
        <form method="POST" action="{{ route('assets.maintenances.store', $asset) }}">
            @csrf
            <div class="grid-3">
                <div class="form-group">
                    <label class="form-label">{{ __('asset.maintenance_date') }} *</label>
                    <input type="date" name="maintenance_date" class="form-control" value="{{ old('maintenance_date', today()->toDateString()) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('asset.maintenance_type') }} *</label>
                    <select name="maintenance_type" class="form-control" required>
                        @foreach($maintenanceTypes as $key => $label)
                        <option value="{{ $key }}" @selected(old('maintenance_type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('asset.cost') }} *</label>
                    <input type="number" step="0.01" min="0" name="cost" class="form-control" value="{{ old('cost', 0) }}" required>
                    <small class="text-muted">{{ __('asset.auto_expense_hint') }}</small>
                </div>
            </div>
            <div class="grid-3">
                <div class="form-group">
                    <label class="form-label">{{ __('asset.vendor_name') }}</label>
                    <input type="text" name="vendor_name" class="form-control" value="{{ old('vendor_name') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('asset.next_service') }}</label>
                    <input type="date" name="next_service_date" class="form-control" value="{{ old('next_service_date') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('asset.remark') }}</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description') }}" placeholder="{{ __('asset.remark_placeholder') }}">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">➕ {{ __('asset.save_maintenance') }}</button>
        </form>
    </div>

    <div class="am-card">
        <h3 class="am-card__title">📋 {{ __('asset.maintenance_history') }}</h3>
        <div class="am-table-wrap table-responsive">
            <table class="am-table">
                <thead>
                    <tr>
                        <th>{{ __('asset.maintenance_date') }}</th>
                        <th>{{ __('asset.maintenance_type') }}</th>
                        <th>{{ __('asset.cost') }}</th>
                        <th>{{ __('asset.vendor_name') }}</th>
                        <th>{{ __('asset.next_service') }}</th>
                        <th>{{ __('asset.remark') }}</th>
                        <th>{{ __('asset.expense') }}</th>
                        <th>{{ __('asset.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asset->maintenances as $m)
                    <tr>
                        <td data-label="Date">{{ $m->maintenance_date->format('d/m/Y') }}</td>
                        <td data-label="Type">{{ $m->type_label }}</td>
                        <td data-label="Cost">{{ $currency }}{{ number_format($m->cost, 0) }}</td>
                        <td data-label="Vendor">{{ $m->vendor_name ?? '—' }}</td>
                        <td data-label="Next">{{ $m->next_service_date?->format('d/m/Y') ?? '—' }}</td>
                        <td data-label="Remark">{{ $m->description ?? '—' }}</td>
                        <td data-label="Expense">
                            @if($m->expense)
                            <span class="badge badge-blue">✓ {{ __('asset.expense_created') }}</span>
                            @elseif($m->cost > 0)
                            <span class="badge badge-yellow">{{ __('asset.pending') }}</span>
                            @else
                            —
                            @endif
                        </td>
                        <td data-label="Action">
                            <form method="POST" action="{{ route('assets.maintenances.destroy', [$asset, $m]) }}" class="am-inline-form" onsubmit="return confirm('{{ __('asset.delete_maintenance_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted" style="padding:24px;">{{ __('asset.no_maintenance') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="am-card">
        <h3 class="am-card__title">💸 {{ __('asset.expense_history') }}</h3>
        <div class="am-table-wrap table-responsive">
            <table class="am-table">
                <thead>
                    <tr>
                        <th>{{ __('asset.maintenance_date') }}</th>
                        <th>{{ __('asset.expense_title') }}</th>
                        <th>{{ __('asset.cost') }}</th>
                        <th>{{ __('asset.reference') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $e)
                    <tr>
                        <td data-label="Date">{{ $e->expense_date->format('d/m/Y') }}</td>
                        <td data-label="Title">{{ $e->description }}</td>
                        <td data-label="Amount">{{ $currency }}{{ number_format($e->amount, 0) }}</td>
                        <td data-label="Ref">MAINT-{{ $e->asset_maintenance_id }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">{{ __('asset.no_expenses') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
