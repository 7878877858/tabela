<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Buffalo;
use App\Models\Feed;

class DailyReportFeed extends Model
{
    protected $table = 'daily_report_feed';

    protected $guarded = [];

    public function buffalo()
    {
        return $this->belongsTo(Buffalo::class);
    }

    public function feedMaster()
    {
        return $this->belongsTo(Feed::class, 'feed_id');
    }

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
}