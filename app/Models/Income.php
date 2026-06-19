<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    public const CATEGORY_MANURE = 'manure_sale';

    public const CATEGORY_ANIMAL = 'animal_sale';

    public const CATEGORY_OTHER = 'other_income';

    protected $fillable = [
        'daily_report_id',
        'income_date',
        'category',
        'description',
        'amount',
        'buffalo_id',
        'buyer_name',
        'weight_kg',
        'rate_per_kg',
        'remarks',
    ];

    protected $casts = [
        'income_date' => 'date',
        'amount'      => 'decimal:2',
        'weight_kg'   => 'decimal:2',
        'rate_per_kg' => 'decimal:2',
    ];

    public function buffalo(): BelongsTo
    {
        return $this->belongsTo(Buffalo::class);
    }

    public function scopeManual(Builder $query): Builder
    {
        return $query->whereIn('category', [
            self::CATEGORY_MANURE,
            self::CATEGORY_ANIMAL,
            self::CATEGORY_OTHER,
        ]);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            self::CATEGORY_MANURE => __('income.manure_sale'),
            self::CATEGORY_ANIMAL => __('income.animal_sale'),
            self::CATEGORY_OTHER  => __('income.other_income'),
            'milk_sale'           => __('income.milk_sale'),
            'calf_sale'           => __('income.calf_sale'),
            'government_subsidy'  => __('income.government_subsidy'),
            'breeding_income'     => __('income.breeding_income'),
            default               => __('income.other_income'),
        };
    }
}
