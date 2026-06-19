<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Expense;
use Illuminate\Http\Request;

class AssetReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('date_from') ?? now()->startOfMonth()->toDateString();
        $to = $request->date('date_to') ?? now()->toDateString();
        $category = $request->get('category', '');
        $status = $request->get('status', '');
        $assetId = $request->get('asset_id', '');
        $report = $request->get('report', 'assets');

        $assetsQuery = Asset::withSum('maintenances as total_maintenance_cost', 'cost')
            ->with('latestMaintenance');

        if ($category) {
            $assetsQuery->where('category', $category);
        }
        if ($status) {
            $assetsQuery->where('status', $status);
        }
        if ($assetId) {
            $assetsQuery->where('id', $assetId);
        }

        $assets = $assetsQuery->orderBy('name')->get();
        $assetsJson = $assets->map(fn (Asset $a) => $a->toGridArray())->values();

        $maintenanceQuery = AssetMaintenance::with('asset')
            ->whereBetween('maintenance_date', [$from, $to]);

        if ($assetId) {
            $maintenanceQuery->where('asset_id', $assetId);
        }
        if ($category) {
            $maintenanceQuery->whereHas('asset', fn ($q) => $q->where('category', $category));
        }

        $maintenances = $maintenanceQuery->orderByDesc('maintenance_date')->get()->map(fn ($m) => [
            'id'                 => $m->id,
            'asset_code'         => $m->asset?->asset_code,
            'asset_name'         => $m->asset?->name,
            'maintenance_date'   => $m->maintenance_date?->format('Y-m-d'),
            'maintenance_type'   => $m->type_label,
            'cost'               => (float) $m->cost,
            'vendor_name'        => $m->vendor_name,
            'description'        => $m->description,
            'next_service_date'  => $m->next_service_date?->format('Y-m-d'),
        ])->values();

        $expenseQuery = Expense::with('assetMaintenance.asset')
            ->where('source', 'asset_module')
            ->whereBetween('expense_date', [$from, $to]);

        if ($assetId) {
            $expenseQuery->whereHas('assetMaintenance', fn ($q) => $q->where('asset_id', $assetId));
        }

        $assetExpenses = $expenseQuery->orderByDesc('expense_date')->get()->map(fn ($e) => [
            'id'          => $e->id,
            'date'        => $e->expense_date?->format('Y-m-d'),
            'description' => $e->description,
            'amount'      => (float) $e->amount,
            'asset_name'  => $e->assetMaintenance?->asset?->name,
            'ref'         => $e->asset_maintenance_id ? 'MAINT-' . $e->asset_maintenance_id : '—',
        ])->values();

        $upcoming = AssetMaintenance::with('asset')
            ->whereNotNull('next_service_date')
            ->whereBetween('next_service_date', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->when($assetId, fn ($q) => $q->where('asset_id', $assetId))
            ->when($category, fn ($q) => $q->whereHas('asset', fn ($aq) => $aq->where('category', $category)))
            ->when($status, fn ($q) => $q->whereHas('asset', fn ($aq) => $aq->where('status', $status)))
            ->orderBy('next_service_date')
            ->get()
            ->map(fn ($m) => [
                'asset_code'        => $m->asset?->asset_code,
                'asset_name'        => $m->asset?->name,
                'category'          => $m->asset?->category_label,
                'next_service_date' => $m->next_service_date?->format('Y-m-d'),
                'last_type'         => $m->type_label,
                'show_url'          => $m->asset ? route('assets.show', $m->asset) : '#',
            ])->values();

        return view('assets.reports', compact(
            'from', 'to', 'category', 'status', 'assetId', 'report',
            'assets', 'assetsJson', 'maintenances', 'assetExpenses', 'upcoming'
        ));
    }
}
