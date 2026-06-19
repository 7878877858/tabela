<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DairyCollection extends Model
{
    protected $fillable = [
        'daily_report_id',
        'date',
        'buffalo_liter',
        'buffalo_fat',
        'buffalo_snf',
        'buffalo_amount',
        'cow_liter',
        'cow_fat',
        'cow_snf',
        'cow_amount',
        'slip_number',
        'slip_image',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'buffalo_liter' => 'decimal:2',
        'buffalo_fat' => 'decimal:2',
        'buffalo_snf' => 'decimal:2',
        'buffalo_amount' => 'decimal:2',
        'cow_liter' => 'decimal:2',
        'cow_fat' => 'decimal:2',
        'cow_snf' => 'decimal:2',
        'cow_amount' => 'decimal:2',
    ];

    public function getTotalLiterAttribute(): float
    {
        return (float) $this->buffalo_liter + (float) $this->cow_liter;
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->buffalo_amount + (float) $this->cow_amount;
    }
}
