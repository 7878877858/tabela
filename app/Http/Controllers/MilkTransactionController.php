<?php

namespace App\Http\Controllers;

use App\Models\Buffalo;
use App\Models\MilkTransaction;
use App\Services\MilkStockService;
use Illuminate\Http\Request;

class MilkTransactionController extends Controller
{
    public function index(Request $request)
    {
        $milkBalance = MilkStockService::currentBalance();

        $transactions = MilkTransaction::with(['buffalo', 'milkSale'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $transactionsJson = $transactions->map(function (MilkTransaction $txn) {
            $buffalo = $txn->buffalo;

            return [
                'id' => $txn->id,
                'date' => $txn->transaction_date->format('Y-m-d'),
                'date_display' => $txn->transaction_date->format('d/m/Y'),
                'transaction_type' => $txn->transaction_type,
                'type_label' => $txn->type_label,
                'direction' => $txn->direction,
                'liters' => (float) $txn->liters,
                'balance_after' => (float) $txn->balance_after,
                'animal_type' => Buffalo::normalizeAnimalType($txn->animal_type ?? $buffalo?->animal_type),
                'buffalo_id' => $txn->buffalo_id,
                'animal_tag' => $buffalo?->tag_number ?? '',
                'animal_name' => $buffalo?->name ?? '',
                'animal_label' => $buffalo?->display_label ?? ($txn->animal_type === 'mixed' ? 'મિશ્ર' : '—'),
                'buyer_name' => $txn->milkSale?->buyer_name ?? '',
                'remarks' => $txn->remarks ?? '',
            ];
        })->values();

        $animalsJson = Buffalo::orderBy('tag_number')->get()->map(fn (Buffalo $b) => [
            'id' => $b->id,
            'tag' => $b->tag_number,
            'name' => $b->name ?? '',
            'animal_type' => Buffalo::normalizeAnimalType($b->animal_type),
            'label' => $b->display_label,
        ])->values();

        return view('milk.transactions', compact(
            'milkBalance',
            'transactionsJson',
            'animalsJson'
        ));
    }
}
