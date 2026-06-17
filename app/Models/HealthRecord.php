<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    protected $fillable = [
        'daily_report_id',
        'buffalo_id',
        'record_date',
        'health_issue',
        'treatment',
        'medicine_cost',
    ];

    protected $casts = [
        'record_date'   => 'date',
        'medicine_cost' => 'decimal:2',
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
