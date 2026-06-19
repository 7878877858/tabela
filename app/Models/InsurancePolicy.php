<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsurancePolicy extends Model
{
    protected $fillable = [
        'insurance_type', 'policy_number', 'premium_amount',
        'start_date', 'expiry_date', 'status', 'remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'premium_amount' => 'decimal:2',
    ];

    public static function types(): array
    {
        return [
            'animal' => 'પશુ વીમો',
            'asset' => 'એસેટ વીમો',
            'vehicle' => 'વાહન વીમો',
        ];
    }
}
