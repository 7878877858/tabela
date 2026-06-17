<?php

namespace App\Http\Controllers;

use App\Models\MilkSale;
use App\Services\MilkStockService;
use Illuminate\Http\Request;

class MilkSaleController extends Controller
{
    public function index(Request $request)
    {
        $milkBalance = MilkStockService::currentBalance();

        $salesJson = MilkSale::orderByDesc('sale_date')
            ->orderByDesc('id')
            ->get()
            ->map(function (MilkSale $s) {
                return [
                    'id' => $s->id,
                    'date' => $s->sale_date->format('Y-m-d'),
                    'date_display' => $s->sale_date->format('d/m/Y'),
                    'liters' => (float) $s->liters_sold,
                    'price_per_liter' => (float) $s->price_per_liter,
                    'amount' => (float) ($s->total_amount ?? ($s->liters_sold * $s->price_per_liter)),
                    'buyer_name' => $s->buyer_name ?? '',
                    'payment_status' => $s->payment_status,
                    'pay_url' => route('sale.pay', $s),
                    'destroy_url' => route('sale.destroy', $s),
                ];
            })->values();

        return view('sale.index', compact('milkBalance', 'salesJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_date'       => 'required|date',
            'liters_sold'     => 'required|numeric|min:0.1',
            'price_per_liter' => 'required|numeric|min:0.1',
            'buyer_name'      => 'nullable|string',
            'payment_status'  => 'required|in:paid,pending',
        ]);

        $available = MilkStockService::currentBalance();
        if ((float) $request->liters_sold > $available) {
            return back()
                ->withErrors([
                    'liters_sold' => 'પૂરતું દૂધ સ્ટોક નથી. ઉપલબ્ધ: ' . number_format($available, 2) . ' L',
                ])
                ->withInput();
        }

        $sale = MilkSale::create($request->all());

        try {
            MilkStockService::recordSale($sale);
        } catch (\InvalidArgumentException $e) {
            $sale->delete();
            return back()->withErrors(['liters_sold' => $e->getMessage()])->withInput();
        }

        return back()->with('success', 'વેચાણ ઉમેરાયું!');
    }

    public function update(Request $request, MilkSale $milkSale)
    {
        $milkSale->update(['payment_status' => 'paid']);
        return back()->with('success', 'પેમેન્ટ મળ્યું!');
    }

    public function destroy(MilkSale $milkSale)
    {
        MilkStockService::reverseSale($milkSale);
        $milkSale->delete();
        return back()->with('success', 'એન્ટ્રી ડિલીટ થઈ.');
    }
}
