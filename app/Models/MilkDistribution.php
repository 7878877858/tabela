<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkDistribution extends Model
{
    protected $fillable = [
        'daily_report_id',
        'date',
        'customer_id',
        'milk_type',
        'morning_liter',
        'evening_liter',
        'rate_per_liter',
        'total_liter',
        'amount',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'morning_liter' => 'decimal:2',
        'evening_liter' => 'decimal:2',
        'rate_per_liter' => 'decimal:2',
        'total_liter' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MilkCustomer::class, 'customer_id');
    }

    public function getMilkTypeLabelAttribute(): string
    {
        return match ($this->milk_type) {
            'cow' => 'ગાય',
            default => 'ભેંસ',
        };
    }

    public static function computeTotals(float $morning, float $evening, float $rate): array
    {
        $totalLiter = round($morning + $evening, 2);
        $amount = round($totalLiter * $rate, 2);

        return ['total_liter' => $totalLiter, 'amount' => $amount];
    }
}
