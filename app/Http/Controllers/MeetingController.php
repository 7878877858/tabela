<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\User;
use App\Models\Employee;
use App\Support\ListPagination;
use App\Support\ListingSearch;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = ListPagination::resolvePerPage($request);
        $search = ListingSearch::term($request->get('search'));

        $meetingQuery = Meeting::latest();
        if ($search) {
            ListingSearch::applyTextColumns($meetingQuery, $search, ['title', 'location', 'status']);
        }

        $meetings = $meetingQuery->paginate($perPage)->withQueryString();

        $meetingStats = [
            'total' => Meeting::count(),
            'today' => Meeting::whereDate('meeting_date', today())->count(),
            'upcoming' => Meeting::whereDate('meeting_date', '>', today())->count(),
            'completed' => Meeting::where('status', 'completed')->count(),
        ];

        return view('meetings.index', compact('meetings', 'meetingStats', 'perPage', 'search'));
    }

    public function create()
    {
        $users = User::all();
        $employees = Employee::all();
            // print_r($users);
            // print_r($employees);
        return view(
            'meetings.create',
            compact('users', 'employees')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'meeting_date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $meeting = Meeting::create([
            'title' => $request->title,
            'description' => $request->description,
            'agenda' => $request->agenda,
            'meeting_date' => $request->meeting_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'created_by' => auth()->id(),
        ]);

       if ($request->participants)
{
    $userIds = [];

    foreach ($request->participants as $participant)
    {
        [$type, $id] = explode('_', $participant);

        if ($type === 'user')
        {
            $userIds[] = (int) $id;
        }
    }

    $meeting->participants()->sync($userIds);
}

        return redirect()
            ->route('meetings.index')
            ->with('success','Meeting Created');
    }

    public function show(Meeting $meeting)
    {
        $meeting->load('participants');

        return view(
            'meetings.show',
            compact('meeting')
        );
    }

    // 👇 આ add કરો
    public function edit(Meeting $meeting)
    {
        $users = User::all();
        $employees = Employee::all();
        return view(
            'meetings.edit',
            compact('meeting','users', 'employees')
        );
    }

   public function update(Request $request, Meeting $meeting)
{
    $request->validate([
        'title' => 'required',
        'meeting_date' => 'required',
        'start_time' => 'required',
        'end_time' => 'required',
    ]);

    $meeting->update([
        'title'        => $request->title,
        'description'  => $request->description,
        'agenda'       => $request->agenda,
        'meeting_date' => $request->meeting_date,
        'start_time'   => $request->start_time,
        'end_time'     => $request->end_time,
        'location'     => $request->location,
        'meeting_link' => $request->meeting_link,
        'status'       => $request->status,
    ]);

    if ($request->participants) {
        $userIds = [];
        foreach ($request->participants as $participant) {
            [$type, $id] = explode('_', $participant);
            if ($type === 'user') {
                $userIds[] = (int) $id;
            }
        }
        $meeting->participants()->sync($userIds);
    }

    return redirect()
        ->route('meetings.index')
        ->with('success', 'Meeting Updated Successfully');
}

    // 👇 આ add કરો
    public function destroy(Meeting $meeting)
    {
        $meeting->delete();

        return redirect()
            ->route('meetings.index')
            ->with('success','Meeting Deleted Successfully');
    }
}