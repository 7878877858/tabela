<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssetMaintenance extends Model
{
    protected $fillable = [
        'asset_id',
        'maintenance_date',
        'maintenance_type',
        'cost',
        'vendor_name',
        'description',
        'next_service_date',
    ];

    protected $casts = [
        'maintenance_date'   => 'date',
        'next_service_date'  => 'date',
        'cost'               => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function expense(): HasOne
    {
        return $this->hasOne(Expense::class, 'asset_maintenance_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return Asset::MAINTENANCE_TYPES[$this->maintenance_type] ?? ucfirst(str_replace('_', ' ', $this->maintenance_type));
    }

    public function isRepair(): bool
    {
        return $this->maintenance_type === 'repair';
    }
}
