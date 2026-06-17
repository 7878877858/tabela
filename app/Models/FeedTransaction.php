<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedTransaction extends Model
{
    protected $table = 'feed_transactions';

    protected $fillable = [
        'feed_id',
        'transaction_type',
        'quantity',
        'rate',
        'total_amount',
        'supplier',
        'direction',
        'balance_after',
        'transaction_date',
        'buffalo_id',
        'daily_report_id',
        'feed_time',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity'         => 'decimal:2',
        'rate'             => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'balance_after'    => 'decimal:2',
    ];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function buffalo(): BelongsTo
    {
        return $this->belongsTo(Buffalo::class);
    }

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLedgerTypeAttribute(): string
    {
        return $this->direction === 'in' ? 'IN' : 'OUT';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'purchase' => 'Stock In',
            'consume'  => 'Consumption',
            'adjust'   => 'Adjustment',
            'return'   => 'Return',
            default    => ucfirst($this->transaction_type),
        };
    }
}
