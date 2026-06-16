<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReportStaff extends Model
{
    protected $table = 'daily_report_staff';

    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
}