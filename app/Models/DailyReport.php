<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = [
        'report_date',
        'shift',
        'report_number',
        'reporter',
        'total_animals',
        'total_milk',
        'present_staff',
        'absent_staff',
        'notes',
        'clean_cowshed',
        'clean_cowshed_by',
        'clean_cowshed_note',

        'clean_milk_room',
        'clean_milk_room_by',
        'clean_milk_room_note',

        'clean_store',
        'clean_store_by',
        'clean_store_note',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function staff()
    {
        return $this->hasMany(DailyReportStaff::class);
    }

    public function milk()
    {
        return $this->hasMany(DailyReportMilk::class);
    }

    public function feed()
    {
        return $this->hasMany(DailyReportFeed::class);
    }

    public function pregnancy()
    {
        return $this->hasMany(DailyReportPregnancy::class);
    }

    public function health()
    {
        return $this->hasMany(DailyReportHealth::class);
    }

    public function expenses()
    {
        return $this->hasMany(DailyReportExpense::class);
    }

    public function incomes()
    {
        return $this->hasMany(DailyReportIncome::class);
    }

    public function vaccinations()
    {
        return $this->hasMany(DailyReportVaccination::class);
    }

    public function syncedMilkEntries()
    {
        return $this->hasMany(MilkEntry::class);
    }

    public function syncedFeedEntries()
    {
        return $this->hasMany(FeedEntry::class);
    }

    public function syncedHealthRecords()
    {
        return $this->hasMany(HealthRecord::class);
    }

    public function syncedVaccinationRecords()
    {
        return $this->hasMany(VaccinationRecord::class);
    }

    public function syncedExpenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function syncedIncomes()
    {
        return $this->hasMany(Income::class);
    }
}
