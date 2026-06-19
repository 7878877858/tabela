<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    public const CATEGORIES = [
        'tractor'      => 'Tractor',
        'trolley'      => 'Trolley',
        'cutter'       => 'Cutter',
        'milk_machine' => 'Milk Machine',
        'generator'    => 'Generator',
        'motor'        => 'Motor',
        'milk_tank'    => 'Milk Tank',
        'shed'         => 'Shed',
        'electric'     => 'Electric Equipment',
        'other'        => 'Other',
    ];

    public const STATUSES = ['active', 'inactive', 'sold', 'scrap'];

    public const MAINTENANCE_TYPES = [
        'service'     => 'Service',
        'repair'      => 'Repair',
        'oil_change'  => 'Oil Change',
        'tyre_change' => 'Tyre Change',
        'other'       => 'Other',
    ];

    protected $fillable = [
        'asset_code',
        'name',
        'category',
        'quantity',
        'purchase_date',
        'purchase_cost',
        'current_value',
        'vendor_name',
        'vendor_mobile',
        'warranty_months',
        'condition',
        'image',
        'status',
        'description',
        'notes',
    ];

    protected $casts = [
        'purchase_date'   => 'date',
        'purchase_cost'   => 'decimal:2',
        'current_value'   => 'decimal:2',
        'warranty_months' => 'integer',
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class)->orderByDesc('maintenance_date');
    }

    public function latestMaintenance(): HasOne
    {
        return $this->hasOne(AssetMaintenance::class)->latestOfMany('maintenance_date');
    }

    public function getNextServiceDateAttribute(): ?string
    {
        $date = $this->maintenances()
            ->whereNotNull('next_service_date')
            ->where('next_service_date', '>=', now()->toDateString())
            ->orderBy('next_service_date')
            ->value('next_service_date');

        if ($date) {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        }

        $fallback = $this->latestMaintenance?->next_service_date;

        return $fallback ? $fallback->format('Y-m-d') : null;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    public function getStatusLabelAttribute(): string
    {
        $key = 'asset.' . $this->status;
        $label = __($key);

        return $label !== $key ? $label : ucfirst((string) $this->status);
    }

    public function getPurchasePriceAttribute(): float
    {
        return (float) ($this->purchase_cost ?? 0);
    }

    public function getAssetNameAttribute(): string
    {
        return (string) $this->name;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }

    public function totalMaintenanceCost(): float
    {
        return (float) $this->maintenances()->sum('cost');
    }

    public function totalRepairCost(): float
    {
        return (float) $this->maintenances()->where('maintenance_type', 'repair')->sum('cost');
    }

    public function toGridArray(): array
    {
        $this->loadMissing('latestMaintenance');
        $latest = $this->latestMaintenance;

        return [
            'id'                    => $this->id,
            'asset_code'            => $this->asset_code,
            'name'                  => $this->name,
            'image_url'             => $this->image_url,
            'category'              => $this->category,
            'category_label'        => $this->category_label,
            'purchase_date'         => $this->purchase_date?->format('Y-m-d'),
            'purchase_price'        => (float) ($this->purchase_cost ?? 0),
            'current_value'         => (float) ($this->current_value ?? 0),
            'vendor_name'           => $this->vendor_name,
            'vendor_mobile'         => $this->vendor_mobile,
            'warranty_months'       => $this->warranty_months,
            'status'                => $this->status,
            'status_label'          => $this->status_label,
            'total_maintenance'     => (float) ($this->total_maintenance_cost ?? $this->maintenances()->sum('cost')),
            'last_maintenance_date' => $latest?->maintenance_date?->format('Y-m-d'),
            'next_service_date'     => $this->next_service_date,
            'show_url'              => route('assets.show', $this),
            'edit_url'              => route('assets.edit', $this),
        ];
    }
}
