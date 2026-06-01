<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MilkSale extends Model
{
    protected $fillable = [
        'sale_date','liters_sold','price_per_liter','buyer_name','payment_status','notes'
    ];
    protected $casts = ['sale_date' => 'date'];
}