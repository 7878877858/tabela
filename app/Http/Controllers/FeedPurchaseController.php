<?php

namespace App\Http\Controllers;

use App\Models\FeedPurchase;
use Illuminate\Http\Request;

class FeedPurchaseController extends Controller
{
    public function index()
    {
        return redirect()
            ->route('feeds.index')
            ->with('info', __('farm.purchase_via_stock_in'));
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('feeds.index')
            ->with('info', __('farm.purchase_via_stock_in'));
    }

    public function destroy(FeedPurchase $feedPurchase)
    {
        return redirect()
            ->route('feeds.index')
            ->with('info', __('farm.purchase_via_stock_in'));
    }
}
