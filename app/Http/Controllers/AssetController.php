<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\AssetCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    protected function validationRules(?Asset $asset = null): array
    {
        return [
            'name'             => 'required|string|max:255',
            'category'         => ['required', Rule::in(array_keys(Asset::CATEGORIES))],
            'purchase_date'    => 'nullable|date',
            'purchase_cost'    => 'nullable|numeric|min:0',
            'current_value'    => 'nullable|numeric|min:0',
            'vendor_name'      => 'nullable|string|max:255',
            'vendor_mobile'    => 'nullable|string|max:20',
            'warranty_months'  => 'nullable|integer|min:0|max:600',
            'status'           => ['required', Rule::in(Asset::STATUSES)],
            'notes'            => 'nullable|string',
            'description'      => 'nullable|string',
            'quantity'         => 'nullable|integer|min:1',
            'condition'        => 'nullable|string|max:50',
            'image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function index()
    {
        $assets = Asset::withSum('maintenances as total_maintenance_cost', 'cost')
            ->with('latestMaintenance')
            ->orderByDesc('id')
            ->get();

        $assetsJson = $assets->map(fn (Asset $a) => $a->toGridArray())->values();
        $categories = Asset::CATEGORIES;
        $statuses = Asset::STATUSES;

        $summary = [
            'total_assets'       => $assets->count(),
            'total_value'        => $assets->sum(fn ($a) => (float) ($a->current_value ?? $a->purchase_cost ?? 0)),
            'month_maintenance'  => \App\Models\AssetMaintenance::whereYear('maintenance_date', now()->year)
                ->whereMonth('maintenance_date', now()->month)
                ->sum('cost'),
            'upcoming_services'  => \App\Models\AssetMaintenance::whereNotNull('next_service_date')
                ->whereBetween('next_service_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->count(),
        ];

        return view('assets.index', compact('assetsJson', 'categories', 'statuses', 'summary'));
    }

    public function create()
    {
        return view('assets.create', [
            'asset'           => new Asset(['status' => 'active', 'category' => 'tractor']),
            'categories'      => Asset::CATEGORIES,
            'statuses'        => Asset::STATUSES,
            'nextCode'        => AssetCodeService::generate(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());
        $data['asset_code'] = AssetCodeService::generate();
        $data['quantity'] = $data['quantity'] ?? 1;
        $data['condition'] = $data['condition'] ?? 'good';

        if (empty($data['current_value']) && !empty($data['purchase_cost'])) {
            $data['current_value'] = $data['purchase_cost'];
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('assets', 'public');
        }

        $asset = Asset::create($data);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', __('asset.created_success'));
    }

    public function show(Asset $asset)
    {
        $asset->load(['maintenances.expense', 'latestMaintenance']);

        $stats = [
            'purchase_price'        => (float) ($asset->purchase_cost ?? 0),
            'current_value'         => (float) ($asset->current_value ?? 0),
            'total_maintenance'     => $asset->totalMaintenanceCost(),
            'total_repairs'         => $asset->totalRepairCost(),
            'last_maintenance_date' => $asset->latestMaintenance?->maintenance_date,
            'next_service_date'     => $asset->next_service_date,
        ];

        $expenses = \App\Models\Expense::whereIn(
            'asset_maintenance_id',
            $asset->maintenances->pluck('id')
        )->orderByDesc('expense_date')->get();

        return view('assets.show', [
            'asset'             => $asset,
            'stats'             => $stats,
            'expenses'          => $expenses,
            'maintenanceTypes'  => Asset::MAINTENANCE_TYPES,
            'categories'        => Asset::CATEGORIES,
        ]);
    }

    public function edit(Asset $asset)
    {
        return view('assets.edit', [
            'asset'      => $asset,
            'categories' => Asset::CATEGORIES,
            'statuses'   => Asset::STATUSES,
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate($this->validationRules($asset));

        if ($request->hasFile('image')) {
            if ($asset->image) {
                Storage::disk('public')->delete($asset->image);
            }
            $data['image'] = $request->file('image')->store('assets', 'public');
        }

        $asset->update($data);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', __('asset.updated_success'));
    }

    public function destroy(Asset $asset)
    {
        foreach ($asset->maintenances as $m) {
            \App\Models\Expense::where('asset_maintenance_id', $m->id)->delete();
        }
        $asset->maintenances()->delete();

        if ($asset->image) {
            Storage::disk('public')->delete($asset->image);
        }

        $asset->delete();

        return redirect()
            ->route('assets.index')
            ->with('success', __('asset.deleted_success'));
    }
}
