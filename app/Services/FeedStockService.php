<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\FeedTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FeedStockService
{
    public static function currentBalance(Feed $feed): float
    {
        $opening = (float) ($feed->volume ?? 0);
        $in = (float) FeedTransaction::where('feed_id', $feed->id)->where('direction', 'in')->sum('quantity');
        $out = (float) FeedTransaction::where('feed_id', $feed->id)->where('direction', 'out')->sum('quantity');

        return max(0, $opening + $in - $out);
    }

    public static function purchase(
        Feed $feed,
        float $quantity,
        ?string $transactionDate = null,
        ?string $remarks = null,
        ?float $rate = null,
        ?string $supplier = null
    ): FeedTransaction {
        $totalAmount = $rate !== null ? round($quantity * $rate, 2) : null;

        return self::record($feed, 'purchase', 'in', $quantity, [
            'transaction_date' => $transactionDate ?? today()->toDateString(),
            'remarks'          => $remarks,
            'rate'             => $rate,
            'total_amount'     => $totalAmount,
            'supplier'         => $supplier,
        ]);
    }

    public static function consume(
        Feed $feed,
        float $quantity,
        array $meta = []
    ): FeedTransaction {
        return self::record($feed, 'consume', 'out', $quantity, $meta);
    }

    public static function consumeFromDailyReportGrid(array $grid, string $reportDate, int $dailyReportId): void
    {
        $periodTotals = [];

        foreach ($grid as $buffaloId => $periods) {
            foreach (['morning', 'evening'] as $period) {
                foreach (($periods[$period] ?? []) as $feedId => $qty) {
                    $qty = (float) $qty;
                    if ($qty <= 0) {
                        continue;
                    }
                    $periodTotals[(int) $feedId][$period] = ($periodTotals[(int) $feedId][$period] ?? 0) + $qty;
                }
            }
        }

        foreach ($periodTotals as $feedId => $periods) {
            $feed = Feed::find($feedId);
            if (!$feed) {
                continue;
            }

            foreach ($periods as $period => $qty) {
                if ($qty <= 0) {
                    continue;
                }

                self::consume($feed, (float) $qty, [
                    'transaction_date' => $reportDate,
                    'daily_report_id'  => $dailyReportId,
                    'feed_time'        => $period,
                    'remarks'          => 'Daily Report — ' . ucfirst($period),
                ]);
            }
        }
    }

    public static function adjust(
        Feed $feed,
        float $quantityDelta,
        ?string $remarks = null
    ): FeedTransaction {
        if ($quantityDelta == 0) {
            throw new InvalidArgumentException('Adjust quantity cannot be zero.');
        }

        $direction = $quantityDelta > 0 ? 'in' : 'out';

        return self::record($feed, 'adjust', $direction, abs($quantityDelta), [
            'transaction_date' => today()->toDateString(),
            'remarks'          => $remarks,
        ]);
    }

    public static function restoreDailyReport(int $dailyReportId): void
    {
        FeedTransaction::where('daily_report_id', $dailyReportId)
            ->where('transaction_type', 'consume')
            ->where('direction', 'out')
            ->delete();
    }

    public static function hasStock(Feed $feed, float $quantity): bool
    {
        return self::currentBalance($feed) >= $quantity;
    }

    protected static function record(
        Feed $feed,
        string $type,
        string $direction,
        float $quantity,
        array $meta = []
    ): FeedTransaction {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($feed, $type, $direction, $quantity, $meta) {
            Feed::where('id', $feed->id)->lockForUpdate()->firstOrFail();

            $current = self::currentBalance($feed->fresh());

            $newBalance = $direction === 'in'
                ? $current + $quantity
                : $current - $quantity;

            if ($newBalance < 0) {
                throw new InvalidArgumentException(
                    $feed->fresh()->name . ' માટે પૂરતો સ્ટોક નથી. ઉપલબ્ધ: ' . number_format($current, 2)
                );
            }

            return FeedTransaction::create([
                'feed_id'          => $feed->id,
                'transaction_type' => $type,
                'quantity'         => $quantity,
                'rate'             => $meta['rate'] ?? null,
                'total_amount'     => $meta['total_amount'] ?? null,
                'supplier'         => $meta['supplier'] ?? null,
                'direction'        => $direction,
                'balance_after'    => $newBalance,
                'transaction_date' => $meta['transaction_date'] ?? today()->toDateString(),
                'buffalo_id'       => $meta['buffalo_id'] ?? null,
                'daily_report_id'  => $meta['daily_report_id'] ?? null,
                'feed_time'        => $meta['feed_time'] ?? null,
                'remarks'          => $meta['remarks'] ?? null,
                'created_by'       => auth()->id(),
            ]);
        });
    }
}
