<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Services\AssetMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetMaintenanceController extends Controller
{
    public function __construct(
        protected AssetMaintenanceService $service
    ) {
    }

    public function store(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'maintenance_date'   => 'required|date',
            'maintenance_type'   => ['required', Rule::in(array_keys(Asset::MAINTENANCE_TYPES))],
            'cost'               => 'required|numeric|min:0',
            'vendor_name'        => 'nullable|string|max:255',
            'description'        => 'nullable|string',
            'next_service_date'  => 'nullable|date|after_or_equal:maintenance_date',
        ]);

        $this->service->create($asset, $data);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', __('asset.maintenance_added'));
    }

    public function update(Request $request, Asset $asset, AssetMaintenance $maintenance)
    {
        abort_unless($maintenance->asset_id === $asset->id, 404);

        $data = $request->validate([
            'maintenance_date'   => 'required|date',
            'maintenance_type'   => ['required', Rule::in(array_keys(Asset::MAINTENANCE_TYPES))],
            'cost'               => 'required|numeric|min:0',
            'vendor_name'        => 'nullable|string|max:255',
            'description'        => 'nullable|string',
            'next_service_date'  => 'nullable|date|after_or_equal:maintenance_date',
        ]);

        $this->service->update($maintenance, $data);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', __('asset.maintenance_updated'));
    }

    public function destroy(Asset $asset, AssetMaintenance $maintenance)
    {
        abort_unless($maintenance->asset_id === $asset->id, 404);

        $this->service->delete($maintenance);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', __('asset.maintenance_deleted'));
    }
}
