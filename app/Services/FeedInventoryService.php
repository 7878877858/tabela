<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\FeedTransaction;

class FeedInventoryService
{
    public static function dashboardStats(): array
    {
        $today = today()->toDateString();

        $feeds = Feed::withInventoryStats()->get();

        return [
            'feed_types'        => $feeds->count(),
            'stock_value'       => $feeds->sum(fn (Feed $f) => $f->estimatedStockValue()),
            'today_consumption' => (float) FeedTransaction::where('direction', 'out')
                ->whereDate('transaction_date', $today)
                ->sum('quantity'),
            'current_inventory' => $feeds->sum(fn (Feed $f) => $f->available_quantity),
        ];
    }

    public static function ledgerForFeed(Feed $feed)
    {
        return FeedTransaction::where('feed_id', $feed->id)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();
    }
}
