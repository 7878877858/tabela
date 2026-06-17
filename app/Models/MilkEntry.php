<?php
// app/Models/MilkEntry.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MilkEntry extends Model
{
    protected $fillable = [
        'buffalo_id',
        'daily_report_id',
        'entry_date',
        'morning_liters',
        'evening_liters',
        'notes',
    ];
    protected $casts = ['entry_date' => 'date'];

    public function buffalo()
    {
        return $this->belongsTo(Buffalo::class);
    }
}