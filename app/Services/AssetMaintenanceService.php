<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class AssetMaintenanceService
{
    public function create(Asset $asset, array $data): AssetMaintenance
    {
        return DB::transaction(function () use ($asset, $data) {
            $maintenance = $asset->maintenances()->create($data);
            $this->syncExpense($maintenance);

            return $maintenance->fresh(['expense', 'asset']);
        });
    }

    public function update(AssetMaintenance $maintenance, array $data): AssetMaintenance
    {
        return DB::transaction(function () use ($maintenance, $data) {
            $maintenance->update($data);
            $this->syncExpense($maintenance->fresh());

            return $maintenance->fresh(['expense', 'asset']);
        });
    }

    public function delete(AssetMaintenance $maintenance): void
    {
        DB::transaction(function () use ($maintenance) {
            Expense::where('asset_maintenance_id', $maintenance->id)->delete();
            $maintenance->delete();
        });
    }

    protected function syncExpense(AssetMaintenance $maintenance): void
    {
        $cost = (float) $maintenance->cost;
        $asset = $maintenance->asset ?? Asset::find($maintenance->asset_id);

        if ($cost <= 0) {
            Expense::where('asset_maintenance_id', $maintenance->id)->delete();

            return;
        }

        $title = ($asset?->name ?? 'Asset') . ' Maintenance';
        if ($maintenance->maintenance_type) {
            $typeLabel = $maintenance->type_label;
            $title .= ' — ' . $typeLabel;
        }

        $payload = [
            'expense_date'          => $maintenance->maintenance_date,
            'category'              => 'equipment',
            'description'           => $title,
            'amount'                => $cost,
            'notes'                 => trim(
                'Source: Asset Module | Ref: MAINT-' . $maintenance->id
                . ($maintenance->description ? ' | ' . $maintenance->description : '')
            ),
            'source'                => 'asset_module',
            'asset_maintenance_id'  => $maintenance->id,
        ];

        $expense = Expense::where('asset_maintenance_id', $maintenance->id)->first();

        if ($expense) {
            $expense->update($payload);
        } else {
            Expense::create($payload);
        }
    }
}
