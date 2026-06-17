<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkTransaction extends Model
{
    protected $fillable = [
        'transaction_type',
        'liters',
        'direction',
        'balance_after',
        'transaction_date',
        'animal_type',
        'buffalo_id',
        'milk_entry_id',
        'milk_sale_id',
        'daily_report_id',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'liters'           => 'decimal:2',
        'balance_after'    => 'decimal:2',
    ];

    public function buffalo(): BelongsTo
    {
        return $this->belongsTo(Buffalo::class);
    }

    public function milkEntry(): BelongsTo
    {
        return $this->belongsTo(MilkEntry::class);
    }

    public function milkSale(): BelongsTo
    {
        return $this->belongsTo(MilkSale::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'production' => 'ઉત્પાદન',
            'sale'       => 'વેચાણ',
            'wastage'    => 'બગાડ / વેડફાર',
            'adjust'     => 'એડજસ્ટમેન્ટ',
            default      => $this->transaction_type,
        };
    }
}
