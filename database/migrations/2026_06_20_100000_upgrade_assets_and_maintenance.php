<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'asset_code')) {
                $table->string('asset_code', 32)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('assets', 'vendor_name')) {
                $table->string('vendor_name')->nullable()->after('purchase_cost');
            }
            if (!Schema::hasColumn('assets', 'vendor_mobile')) {
                $table->string('vendor_mobile', 20)->nullable()->after('vendor_name');
            }
            if (!Schema::hasColumn('assets', 'warranty_months')) {
                $table->unsignedSmallInteger('warranty_months')->nullable()->after('vendor_mobile');
            }
            if (!Schema::hasColumn('assets', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });

        if (Schema::hasColumn('assets', 'status')) {
            DB::statement("ALTER TABLE assets MODIFY status VARCHAR(20) NOT NULL DEFAULT 'active'");
        }

        $assets = DB::table('assets')->whereNull('asset_code')->orderBy('id')->get();
        foreach ($assets as $asset) {
            DB::table('assets')->where('id', $asset->id)->update([
                'asset_code' => 'AST-' . str_pad((string) $asset->id, 5, '0', STR_PAD_LEFT),
            ]);
        }

        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->date('maintenance_date');
            $table->string('maintenance_type', 64);
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('vendor_name')->nullable();
            $table->text('description')->nullable();
            $table->date('next_service_date')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'maintenance_date']);
            $table->index('next_service_date');
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'source')) {
                $table->string('source', 64)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('expenses', 'asset_maintenance_id')) {
                $table->foreignId('asset_maintenance_id')
                    ->nullable()
                    ->after('source')
                    ->constrained('asset_maintenances')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'asset_maintenance_id')) {
                $table->dropConstrainedForeignId('asset_maintenance_id');
            }
            if (Schema::hasColumn('expenses', 'source')) {
                $table->dropColumn('source');
            }
        });

        Schema::dropIfExists('asset_maintenances');

        Schema::table('assets', function (Blueprint $table) {
            foreach (['asset_code', 'vendor_name', 'vendor_mobile', 'warranty_months', 'notes'] as $col) {
                if (Schema::hasColumn('assets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
