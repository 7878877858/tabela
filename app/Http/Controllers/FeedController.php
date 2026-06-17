<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Feed;
use App\Models\FeedTransaction;
use App\Services\FeedInventoryService;
use App\Services\FeedStockService;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index()
    {
        $feeds = Feed::withInventoryStats()
            ->orderBy('name')
            ->paginate(25);

        $allFeeds = Feed::orderBy('name')->get(['id', 'name', 'unit']);
        $stats = FeedInventoryService::dashboardStats();

        return view('feeds.index', compact('feeds', 'allFeeds', 'stats'));
    }

    public function history(Request $request)
    {
        $query = FeedTransaction::with('feed')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        if ($request->filled('feed_id')) {
            $query->where('feed_id', $request->feed_id);
        }

        if ($request->filled('transaction_type')) {
            $type = strtoupper($request->transaction_type);
            $query->where('direction', $type === 'IN' ? 'in' : 'out');
        }

        $transactions = $query->paginate(50)->withQueryString();
        $feeds = Feed::orderBy('name')->get(['id', 'name', 'unit']);

        return view('feeds.history', compact('transactions', 'feeds'));
    }

    public function create()
    {
        return view('feeds.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'unit'       => 'nullable|string|max:50',
            'min_stock'  => 'nullable|numeric|min:0',
            'volume'     => 'nullable|numeric|min:0',
        ]);

        $feed = Feed::create([
            'name'        => $request->name,
            'volume'      => 0,
            'unit'        => $request->unit ?? 'Kg',
            'min_stock'   => $request->min_stock ?? 0,
            'description' => $request->description,
            'status'      => $request->status ?? 1,
        ]);

        $initialVolume = (float) ($request->volume ?? 0);
        if ($initialVolume > 0) {
            FeedStockService::purchase(
                $feed,
                $initialVolume,
                today()->toDateString(),
                'Initial stock on feed create',
                $request->rate ? (float) $request->rate : null,
                $request->supplier
            );
        }

        return redirect()
            ->route('feeds.index')
            ->with('success', 'Feed Added Successfully');
    }

    public function edit(Feed $feed)
    {
        $feed->loadSum(['transactions as total_in' => fn ($q) => $q->where('direction', 'in')], 'quantity')
            ->loadSum(['transactions as total_out' => fn ($q) => $q->where('direction', 'out')], 'quantity');

        return view('feeds.edit', compact('feed'));
    }

    public function update(Request $request, Feed $feed)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'min_stock' => 'nullable|numeric|min:0',
        ]);

        $feed->update([
            'name'        => $request->name,
            'unit'        => $request->unit,
            'min_stock'   => $request->min_stock ?? 0,
            'description' => $request->description,
            'status'      => $request->status,
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
        $feed->loadSum(['transactions as total_in' => fn ($q) => $q->where('direction', 'in')], 'quantity')
            ->loadSum(['transactions as total_out' => fn ($q) => $q->where('direction', 'out')], 'quantity')
            ->loadSum(['transactions as stock_value_in' => fn ($q) => $q->where('direction', 'in')], 'total_amount');

        $ledger = FeedInventoryService::ledgerForFeed($feed);

        $transactions = $feed->transactions()->with(['buffalo', 'creator', 'dailyReport'])->paginate(25);

        return view('feeds.show', compact('feed', 'transactions', 'ledger'));
    }

    public function stockIn(Request $request, Feed $feed)
    {
        $request->validate([
            'quantity'         => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'rate'             => 'nullable|numeric|min:0',
            'supplier'         => 'nullable|string|max:255',
            'remarks'          => 'nullable|string|max:500',
            'purchase_amount'  => 'nullable|numeric|min:0',
        ]);

        $rate = $request->filled('rate') ? (float) $request->rate : null;

        FeedStockService::purchase(
            $feed,
            (float) $request->quantity,
            $request->transaction_date,
            $request->remarks ?? 'Stock In',
            $rate,
            $request->supplier
        );

        $amount = $request->filled('purchase_amount')
            ? (float) $request->purchase_amount
            : ($rate ? (float) $request->quantity * $rate : null);

        if ($amount && $amount > 0) {
            Expense::create([
                'expense_date' => $request->transaction_date,
                'category'     => 'feed',
                'description'  => $feed->name . ' — ' . ($request->remarks ?? 'Stock In'),
                'amount'       => $amount,
            ]);
        }

        return back()->with('success', 'Stock added successfully!');
    }
}
