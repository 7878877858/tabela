<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedEntry extends Model
{
    protected $fillable = [
        'daily_report_id',
        'buffalo_id',
        'feed_id',
        'entry_date',
        'feed_time',
        'quantity',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'quantity'   => 'decimal:2',
    ];

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function buffalo(): BelongsTo
    {
        return $this->belongsTo(Buffalo::class);
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }
}
