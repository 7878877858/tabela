<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalTransaction extends Model
{
    protected $fillable = [
        'transaction_type', 'buffalo_id', 'amount', 'counterparty_name',
        'transaction_date', 'remarks', 'income_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function buffalo(): BelongsTo
    {
        return $this->belongsTo(Buffalo::class);
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->transaction_type === 'purchase' ? 'ખરીદી' : 'વેચાણ';
    }
}
