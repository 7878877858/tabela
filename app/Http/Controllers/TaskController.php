<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use App\Support\ListPagination;
use App\Support\ListingSearch;
use Illuminate\Http\Request;

class TaskController extends Controller
{
     public function index(Request $request)
    {
        $perPage = ListPagination::resolvePerPage($request);
        $search = ListingSearch::term($request->get('search'));

        $taskQuery = Task::with('employee')->latest();
        if ($search) {
            $term = ListingSearch::likeTerm($search);
            $taskQuery->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhereHas('employee', fn ($e) => $e->where('name', 'like', $term));
            });
        }

        $taskStats = [
            'total' => Task::count(),
            'pending' => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'completed' => Task::where('status', 'completed')->count(),
        ];

        $tasks = $taskQuery->paginate($perPage)->withQueryString();
        $employees = Employee::orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'employees', 'taskStats', 'perPage', 'search'));
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