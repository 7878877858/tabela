<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaccinationRecord extends Model
{
    protected $fillable = [
        'daily_report_id',
        'buffalo_id',
        'vaccine_name',
        'vaccination_date',
        'remarks',
    ];

    protected $casts = [
        'vaccination_date' => 'date',
    ];

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function buffalo(): BelongsTo
    {
        return $this->belongsTo(Buffalo::class);
    }
}
