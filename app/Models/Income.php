<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    protected $fillable = [
        'daily_report_id',
        'income_date',
        'category',
        'description',
        'amount',
        'buffalo_id',
    ];

    protected $casts = [
        'income_date' => 'date',
        'amount'      => 'decimal:2',
    ];

    public function buffalo(): BelongsTo
    {
        return $this->belongsTo(Buffalo::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'milk_sale'          => __('income.milk_sale'),
            'animal_sale'        => __('income.animal_sale'),
            'calf_sale'          => __('income.calf_sale'),
            'government_subsidy' => __('income.government_subsidy'),
            'breeding_income'    => __('income.breeding_income'),
            'manure_sale'        => __('income.manure_sale'),
            default              => __('income.other_income'),
        };
    }
}
