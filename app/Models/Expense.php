<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'daily_report_id',
        'expense_date',
        'category',
        'description',
        'amount',
        'buffalo_id',
        'notes',
        'source',
        'asset_maintenance_id',
    ];
    protected $casts = ['expense_date' => 'date'];

    public function buffalo() { return $this->belongsTo(Buffalo::class); }

    public function assetMaintenance() { return $this->belongsTo(AssetMaintenance::class); }

   public function getCategoryLabelAttribute()
{
    return match($this->category) {
        'feed' => __('kharch.feed'),
        'medicine' => __('kharch.medicine'),
        'labour' => __('kharch.labour'),
        'equipment' => __('kharch.equipment'),
        'veterinary' => __('kharch.veterinary'),
        default => __('kharch.other'),
    };
}
}

// ─────────────────────────────────────────────────
// Save as app/Models/MilkSale.php
// ─────────────────────────────────────────────────
// namespace App\Models;
// use Illuminate\Database\Eloquent\Model;
// class MilkSale extends Model { ... }
// (Included inline for brevity — see MilkSaleModel.php)