<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReportFeed extends Model
{
    protected $table = 'daily_report_feed';

    protected $fillable = [
        'daily_report_id',
        'buffalo_id',
        'morning_feeds',
        'evening_feeds',
        'total_feed',
    ];

    protected $casts = [
        'morning_feeds' => 'array',
        'evening_feeds' => 'array',
        'total_feed'    => 'decimal:2',
    ];

    public function buffalo()
    {
        return $this->belongsTo(Buffalo::class);
    }

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function qtyFor(string $period, int|string $feedId): float
    {
        $map = $period === 'evening' ? ($this->evening_feeds ?? []) : ($this->morning_feeds ?? []);

        return (float) ($map[(string) $feedId] ?? $map[$feedId] ?? 0);
    }

    public function morningTotal(): float
    {
        return (float) array_sum(array_map('floatval', $this->morning_feeds ?? []));
    }

    public function eveningTotal(): float
    {
        return (float) array_sum(array_map('floatval', $this->evening_feeds ?? []));
    }
}
