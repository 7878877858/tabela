<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use Illuminate\Http\Request;

class TaskController extends Controller
{
     public function index()
    {
        $tasks = Task::latest()->get();
        $employees = Employee::orderBy('name')->get();

        return view('tasks.index', compact('tasks','employees'));
    }

    public function create()
    {
        //
    }

   public function store(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'title'       => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority'    => 'required',
        'recurrence'  => 'required',
        'status'      => 'required',
        'start_date'  => 'nullable|date',
        'due_date'    => 'nullable|date',
    ]);

    Task::create($request->all());

    return redirect()
        ->route('tasks.index')
        ->with('success','Task Assigned Successfully');
}

    public function show(Task $task)
    {
        //
    }

    public function edit(Task $task)
{
    $employees = Employee::orderBy('name')->get();

    return view('tasks.edit', compact('task','employees'));
}

    public function update(Request $request, Task $task)
{
    $request->validate([
        'employee_id' => 'required',
        'title' => 'required',
        'priority' => 'required',
        'status' => 'required',
    ]);

    $task->update($request->all());

    return redirect()
        ->route('tasks.index')
        ->with('success','Task Updated Successfully');
}

public function destroy(Task $task)
{
    $task->delete();

    return redirect()
        ->route('tasks.index')
        ->with('success','Task Deleted');
}

public function complete(Task $task)
{
    $task->update([
        'status' => 'completed'
    ]);

    return back()
        ->with('success','Task Completed');
}
}