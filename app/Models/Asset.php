<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
    'name',
    'category',
    'quantity',
    'purchase_date',
    'purchase_cost',
    'current_value',
    'condition',
    'image',
    'status',
    'description'
];
}
