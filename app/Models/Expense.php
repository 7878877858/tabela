<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_date','category','description','amount','buffalo_id','notes'
    ];
    protected $casts = ['expense_date' => 'date'];

    public function buffalo() { return $this->belongsTo(Buffalo::class); }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'feed'       => 'ચારો / ઘાસ',
            'medicine'   => 'દવા',
            'labour'     => 'મજૂરી',
            'equipment'  => 'સાધન',
            'veterinary' => 'પશુ ડૉક્ટર',
            'other'      => 'અન્ય',
            default      => $this->category,
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