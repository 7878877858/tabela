<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MilkCustomer extends Model
{
    protected $fillable = [
        'name',
        'mobile',
        'address',
        'status',
    ];

    public function distributions(): HasMany
    {
        return $this->hasMany(MilkDistribution::class, 'customer_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getDisplayLabelAttribute(): string
    {
        if ($this->mobile) {
            return $this->name . ' - ' . $this->mobile;
        }

        return $this->name;
    }
}
