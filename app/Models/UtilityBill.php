<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityBill extends Model
{
    protected $fillable = [
        'bill_type', 'amount', 'bill_date', 'due_date', 'paid_date', 'status', 'remarks',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public static function billTypes(): array
    {
        return [
            'electricity' => 'વીજળી',
            'water' => 'પાણી',
            'internet' => 'ઇન્ટરનેટ',
            'phone' => 'ફોન',
            'other' => 'અન્ય',
        ];
    }
}
