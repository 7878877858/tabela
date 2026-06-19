<?php

namespace App\Services;

use App\Models\Buffalo;
use App\Models\MilkEntry;
use App\Models\MilkSale;
use App\Models\MilkTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MilkStockService
{
    public static function currentBalance(): float
    {
        $last = MilkTransaction::orderByDesc('id')->value('balance_after');

        return (float) ($last ?? 0);
    }

    public static function syncProduction(MilkEntry $entry): void
    {
        $entry->loadMissing('buffalo');
        $newLiters = (float) $entry->total_liters;

        $recorded = (float) MilkTransaction::where('milk_entry_id', $entry->id)
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN liters ELSE -liters END) as net")
            ->value('net');

        $delta = $newLiters - $recorded;

        if (abs($delta) < 0.001) {
            return;
        }

        $meta = [
            'transaction_date' => $entry->entry_date->toDateString(),
            'buffalo_id'       => $entry->buffalo_id,
            'milk_entry_id'    => $entry->id,
            'animal_type'      => self::resolveAnimalType($entry->buffalo?->animal_type),
        ];

        if ($delta > 0) {
            self::record('production', 'in', $delta, array_merge($meta, [
                'remarks' => 'Milk production',
            ]));
            return;
        }

        self::record('adjust', 'out', abs($delta), array_merge($meta, [
            'remarks' => 'Production reduced',
        ]));
    }

    public static function recordSale(MilkSale $sale): MilkTransaction
    {
        return self::record('sale', 'out', (float) $sale->liters_sold, [
            'transaction_date' => $sale->sale_date->toDateString(),
            'milk_sale_id'     => $sale->id,
            'animal_type'      => 'mixed',
            'remarks'          => $sale->buyer_name ? 'Buyer: ' . $sale->buyer_name : 'Milk sale',
        ]);
    }

    public static function reverseSale(MilkSale $sale): void
    {
        $txn = MilkTransaction::where('milk_sale_id', $sale->id)->first();
        if (!$txn) {
            return;
        }

        self::record('adjust', 'in', (float) $txn->liters, [
            'transaction_date' => today()->toDateString(),
            'remarks'          => 'Sale deleted #' . $sale->id,
        ]);
    }

    public static function reverseProduction(MilkEntry $entry): void
    {
        $recorded = (float) MilkTransaction::where('milk_entry_id', $entry->id)
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN liters ELSE -liters END) as net")
            ->value('net');

        if ($recorded <= 0) {
            return;
        }

        self::record('adjust', 'out', $recorded, [
            'transaction_date' => $entry->entry_date->toDateString(),
            'buffalo_id'       => $entry->buffalo_id,
            'milk_entry_id'    => $entry->id,
            'animal_type'      => self::resolveAnimalType($entry->buffalo?->animal_type),
            'remarks'          => 'Milk entry deleted',
        ]);
    }

    protected static function resolveAnimalType(?string $type): string
    {
        return Buffalo::normalizeAnimalType($type ?? 'buffalo');
    }

    protected static function record(
        string $type,
        string $direction,
        float $liters,
        array $meta = []
    ): MilkTransaction {
        if ($liters <= 0) {
            throw new InvalidArgumentException('Liters must be greater than zero.');
        }

        return DB::transaction(function () use ($type, $direction, $liters, $meta) {
            $current = self::currentBalance();

            $newBalance = $direction === 'in'
                ? $current + $liters
                : $current - $liters;

            if ($newBalance < 0) {
                throw new InvalidArgumentException(
                    'પૂરતું દૂધ સ્ટોક નથી. ઉપલબ્ધ: ' . number_format($current, 2) . ' L'
                );
            }

            return MilkTransaction::create([
                'transaction_type' => $type,
                'liters'           => $liters,
                'direction'        => $direction,
                'balance_after'    => $newBalance,
                'transaction_date' => $meta['transaction_date'] ?? today()->toDateString(),
                'animal_type'      => isset($meta['animal_type'])
                    ? self::resolveAnimalType($meta['animal_type'])
                    : null,
                'buffalo_id'       => $meta['buffalo_id'] ?? null,
                'milk_entry_id'    => $meta['milk_entry_id'] ?? null,
                'milk_sale_id'     => $meta['milk_sale_id'] ?? null,
                'daily_report_id'  => $meta['daily_report_id'] ?? null,
                'remarks'          => $meta['remarks'] ?? null,
                'created_by'       => auth()->id(),
            ]);
        });
    }
}
