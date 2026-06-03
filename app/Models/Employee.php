<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Task;
class Employee extends Model
{
    protected $fillable = ['name','employee_type','mobile','join_date','monthly_salary','status','notes'];
    protected $casts    = ['join_date' => 'date'];

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function totalPaid(): float
    {
        return $this->salaryPayments()->where('status','paid')->sum('amount');
    }

    public function pendingMonths(): int
    {
        $paid = $this->salaryPayments()
            ->selectRaw('CONCAT(year,"-",LPAD(month,2,"0")) as ym')
            ->pluck('ym')->toArray();

        $start  = $this->join_date->startOfMonth();
        $end    = now()->startOfMonth();
        $months = 0;

        while ($start <= $end) {
            $ym = $start->format('Y-m');
            if (!in_array($ym, $paid)) $months++;
            $start->addMonth();
        }
        return $months;
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
    
}