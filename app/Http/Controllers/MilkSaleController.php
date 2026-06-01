<?php
namespace App\Http\Controllers;

use App\Models\MilkSale;
use Illuminate\Http\Request;

class MilkSaleController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $sales = MilkSale::whereYear('sale_date', $year)
            ->whereMonth('sale_date', $month)
            ->orderByDesc('sale_date')
            ->paginate(25);

        $totalLiters = MilkSale::whereYear('sale_date', $year)->whereMonth('sale_date', $month)->sum('liters_sold');
        $totalIncome = MilkSale::whereYear('sale_date', $year)->whereMonth('sale_date', $month)->sum('total_amount');
        $pending     = MilkSale::whereYear('sale_date', $year)->whereMonth('sale_date', $month)->where('payment_status','pending')->sum('total_amount');

        return view('sale.index', compact('sales','totalLiters','totalIncome','pending','month','year'));
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

        MilkSale::create($request->all());
        return back()->with('success', 'વેચાણ ઉમેરાયું!');
    }

    public function update(Request $request, MilkSale $milkSale)
    {
        $milkSale->update(['payment_status' => 'paid']);
        return back()->with('success', 'પેમેન્ટ મળ્યું!');
    }

    public function destroy(MilkSale $milkSale)
    {
        $milkSale->delete();
        return back()->with('success', 'એન્ટ્રી ડિલીટ થઈ.');
    }
}