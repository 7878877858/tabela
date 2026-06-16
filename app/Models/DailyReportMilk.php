<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReportMilk extends Model
{
    protected $table = 'daily_report_milk';

    protected $guarded = [];

    public function buffalo()
    {
        return $this->belongsTo(Buffalo::class);
    }

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
}