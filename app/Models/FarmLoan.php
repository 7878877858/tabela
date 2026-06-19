<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmLoan extends Model
{
    protected $table = 'farm_loans';

    protected $fillable = [
        'loan_name', 'bank_name', 'loan_amount', 'emi_amount',
        'start_date', 'end_date', 'outstanding_balance', 'remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'loan_amount' => 'decimal:2',
        'emi_amount' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];
}
