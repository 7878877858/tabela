<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index()
    {
        $feeds = Feed::latest()->paginate(20);

        return view('feeds.index', compact('feeds'));
    }

    public function create()
    {
        return view('feeds.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        Feed::create([
            'name' => $request->name,
            'volume' => $request->volume,
            'unit' => $request->unit,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('feeds.index')
            ->with('success', 'Feed Added Successfully');
    }

    public function edit(Feed $feed)
    {
        return view('feeds.edit', compact('feed'));
    }

    public function update(Request $request, Feed $feed)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $feed->update([
            'name' => $request->name,
            'volume' => $request->volume,
            'unit' => $request->unit,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('feeds.index')
            ->with('success', 'Feed Updated Successfully');
    }

    public function destroy(Feed $feed)
    {
        $feed->delete();

        return redirect()
            ->route('feeds.index')
            ->with('success', 'Feed Deleted Successfully');
    }
    public function show(Feed $feed)
    {
        return view('feeds.show', compact('feed'));
    }
}
