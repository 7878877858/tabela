<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryPayment;
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
            'employee_type'  => 'required|in:employee,committee',
            'mobile'         => 'nullable|string|max:15',
            'join_date'      => 'required|date',
            'monthly_salary' => 'required|numeric|min:0',
        ]);

        Employee::create([
            'name'           => $request->name,
            'employee_type'  => $request->employee_type,
            'mobile'         => $request->mobile,
            'join_date'      => $request->join_date,
            'monthly_salary' => $request->monthly_salary,
            'status'         => 'active',
        ]);

        return back()->with('success', 'કર્મચારી ઉમેરાયા!');
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'monthly_salary' => 'required|numeric|min:0',
            'status'         => 'required|in:active,inactive',
            'employee_type'  => 'required|in:employee,committee',
        ]);

        $employee->update([
            'employee_type'  => $request->employee_type,
            'monthly_salary' => $request->monthly_salary,
            'status'         => $request->status,
            'mobile'         => $request->mobile,
            'notes'          => $request->notes,
        ]);

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
            [
                'employee_id' => $employee->id,
                'month'       => $request->month,
                'year'        => $request->year,
            ],
            [
                'payment_date' => today(),
                'amount'       => $request->amount,
                'status'       => 'paid',
            ]
        );

        return back()->with('success', 'પગાર ચૂકવ્યો!');
    }

    public function portal(Employee $employee)
    {
        $tasks = $employee->tasks()
            ->latest()
            ->get();

        return view(
            'employees.portal',
            compact('employee','tasks')
        );
    }


}