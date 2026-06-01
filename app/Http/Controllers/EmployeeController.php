<?php
namespace App\Http\Controllers;

use App\Models\{Employee, SalaryPayment};
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('name')->paginate(20);
        return view('employees.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'mobile'         => 'nullable|string|max:15',
            'join_date'      => 'required|date',
            'monthly_salary' => 'required|numeric|min:0',
        ]);

        Employee::create($request->all());
        return back()->with('success', 'કર્મચારી ઉમેરાયા!');
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'monthly_salary' => 'required|numeric|min:0',
            'status'         => 'required|in:active,inactive',
        ]);

        $employee->update($request->only('monthly_salary','status','mobile','notes'));
        return back()->with('success', 'અપડેટ થઈ ગઈ!');
    }

    public function paySalary(Request $request, Employee $employee)
    {
        $request->validate([
            'month'  => 'required|integer|min:1|max:12',
            'year'   => 'required|integer|min:2020',
            'amount' => 'required|numeric|min:0',
        ]);

        SalaryPayment::updateOrCreate(
            ['employee_id' => $employee->id, 'month' => $request->month, 'year' => $request->year],
            ['payment_date' => today(), 'amount' => $request->amount, 'status' => 'paid']
        );

        return back()->with('success', 'પગાર ચૂકવ્યો!');
    }
}