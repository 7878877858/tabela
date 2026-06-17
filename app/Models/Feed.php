<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    protected $fillable = [
        'name',
        'volume',
        'min_stock',
        'unit',
        'description',
        'status',
    ];

    protected $casts = [
        'min_stock' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(FeedTransaction::class)->orderByDesc('transaction_date')->orderByDesc('id');
    }

    public function scopeWithInventoryStats(Builder $query): Builder
    {
        return $query
            ->withSum(['transactions as total_in' => fn ($q) => $q->where('direction', 'in')], 'quantity')
            ->withSum(['transactions as total_out' => fn ($q) => $q->where('direction', 'out')], 'quantity')
            ->withSum(['transactions as stock_value_in' => fn ($q) => $q->where('direction', 'in')], 'total_amount');
    }

    public function getOpeningStockAttribute(): float
    {
        return (float) ($this->volume ?? 0);
    }

    public function getAvailableQuantityAttribute(): float
    {
        $opening = $this->opening_stock;

        if (array_key_exists('total_in', $this->attributes) || array_key_exists('total_out', $this->attributes)) {
            $in = (float) ($this->attributes['total_in'] ?? 0);
            $out = (float) ($this->attributes['total_out'] ?? 0);

            return max(0, $opening + $in - $out);
        }

        $in = (float) $this->transactions()->where('direction', 'in')->sum('quantity');
        $out = (float) $this->transactions()->where('direction', 'out')->sum('quantity');

        return max(0, $opening + $in - $out);
    }

    public function isLowStock(): bool
    {
        $threshold = (float) ($this->min_stock ?? 0);

        return $threshold > 0 && $this->available_quantity < $threshold;
    }

    public function averageInRate(): float
    {
        return (float) $this->transactions()
            ->where('direction', 'in')
            ->whereNotNull('rate')
            ->avg('rate');
    }

    public function estimatedStockValue(): float
    {
        return $this->available_quantity * $this->averageInRate();
    }
}
