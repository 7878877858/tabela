<?php
// app/Models/Buffalo.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buffalo extends Model
{
     protected $table = 'buffaloes'; // 🔥 FIX
    protected $fillable = [
        'tag_number','name','dob','purchase_date',
        'purchase_price','status','lactation_status','notes'
    ];

    protected $casts = ['dob' => 'date', 'purchase_date' => 'date'];

    public function milkEntries(): HasMany
    {
        return $this->hasMany(MilkEntry::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function todayMilk()
    {
        return $this->milkEntries()->whereDate('entry_date', today())->first();
    }

    public function totalMilkThisMonth(): float
    {
        return $this->milkEntries()
            ->whereYear('entry_date', now()->year)
            ->whereMonth('entry_date', now()->month)
            ->sum('total_liters');
    }

    public function getStatusLabelAttribute(): string
{
    return match($this->status) {
        'active' => __('buffalo.active'),
        'dry'    => __('buffalo.dry'),
        'sold'   => __('buffalo.sold'),
        'dead'   => __('buffalo.dead'),
        default  => $this->status,
    };
}

public function getLactationLabelAttribute(): string
{
    return match($this->lactation_status) {
        'lactating' => __('buffalo.lactating'),
        'dry'       => __('buffalo.dry'),
        'pregnant'  => __('buffalo.pregnant'),
        default     => $this->lactation_status,
    };
}
}