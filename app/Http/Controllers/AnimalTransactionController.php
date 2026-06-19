<?php

namespace App\Http\Controllers;

use App\Models\AnimalTransaction;
use App\Services\AnimalTransactionService;
use App\Support\ListPagination;
use Illuminate\Http\Request;

class AnimalTransactionController extends Controller
{
    public function __construct(
        protected AnimalTransactionService $service
    ) {
    }

    public function index(Request $request)
    {
        $perPage = ListPagination::resolvePerPage($request);
        $type = $request->get('type');

        $query = AnimalTransaction::with('buffalo')->orderByDesc('transaction_date')->orderByDesc('id');
        if ($type && in_array($type, ['purchase', 'sale'], true)) {
            $query->where('transaction_type', $type);
        }

        $transactions = $query->paginate($perPage)->withQueryString();

        return view('animal-transactions.index', compact('transactions', 'perPage', 'type'));
    }

    public function createPurchase()
    {
        return redirect()
            ->route('buffalo.create')
            ->with('info', __('farm.purchase_via_animal_form'));
    }

    public function storePurchase(Request $request)
    {
        return redirect()
            ->route('buffalo.create')
            ->with('info', __('farm.purchase_via_animal_form'));
    }

    public function createSale()
    {
        return redirect()->route('reports.animal-sales');
    }

    public function storeSale(Request $request)
    {
        return redirect()->route('reports.animal-sales');
    }
}
