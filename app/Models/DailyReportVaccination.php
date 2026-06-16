<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReportVaccination extends Model
{
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