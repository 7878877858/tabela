<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmOtherExpense extends Model
{
    protected $fillable = ['category', 'amount', 'expense_date', 'remarks'];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];
}
