<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    protected $fillable = ['employee_id','payment_date','month','year','amount','status','notes'];
    protected $casts    = ['payment_date' => 'date'];

    public function employee() { return $this->belongsTo(Employee::class); }
}