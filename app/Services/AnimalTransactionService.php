<?php

namespace App\Services;

use App\Models\AnimalTransaction;
use App\Models\Buffalo;
use App\Models\Income;
use Illuminate\Support\Facades\DB;

class AnimalTransactionService
{
    public function syncPurchaseFromAnimal(Buffalo $animal): ?AnimalTransaction
    {
        $amount = (float) $animal->purchase_price;
        if ($amount <= 0) {
            return null;
        }

        $transactionDate = $animal->purchase_date
            ?? $animal->created_at?->toDateString()
            ?? now()->toDateString();

        $existing = AnimalTransaction::query()
            ->where('buffalo_id', $animal->id)
            ->where('transaction_type', 'purchase')
            ->first();

        if ($existing) {
            return $existing;
        }

        return AnimalTransaction::create([
            'transaction_type' => 'purchase',
            'buffalo_id' => $animal->id,
            'amount' => $amount,
            'counterparty_name' => null,
            'transaction_date' => $transactionDate,
            'remarks' => __('farm.purchase_from_animal_registry'),
        ]);
    }

    public function recordSale(array $data): AnimalTransaction
    {
        return DB::transaction(function () use ($data) {
            $animal = Buffalo::findOrFail($data['buffalo_id']);

            if ($animal->status !== 'active') {
                throw new \InvalidArgumentException(__('farm.animal_not_active'));
            }

            $income = Income::create([
                'income_date' => $data['transaction_date'],
                'category' => Income::CATEGORY_ANIMAL,
                'description' => __('income.animal_sale') . ' — ' . $animal->display_label,
                'amount' => $data['amount'],
                'buffalo_id' => $animal->id,
                'buyer_name' => $data['counterparty_name'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $animal->update([
                'status' => 'sold',
                'sold_date' => $data['transaction_date'],
                'sale_price' => $data['amount'],
                'buyer_name' => $data['counterparty_name'] ?? null,
            ]);

            return AnimalTransaction::create([
                'transaction_type' => 'sale',
                'buffalo_id' => $animal->id,
                'amount' => $data['amount'],
                'counterparty_name' => $data['counterparty_name'] ?? null,
                'transaction_date' => $data['transaction_date'],
                'remarks' => $data['remarks'] ?? null,
                'income_id' => $income->id,
            ]);
        });
    }
}
