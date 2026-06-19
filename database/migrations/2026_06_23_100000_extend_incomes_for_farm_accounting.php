<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            if (!Schema::hasColumn('incomes', 'buyer_name')) {
                $table->string('buyer_name')->nullable()->after('buffalo_id');
            }
            if (!Schema::hasColumn('incomes', 'weight_kg')) {
                $table->decimal('weight_kg', 10, 2)->nullable()->after('buyer_name');
            }
            if (!Schema::hasColumn('incomes', 'rate_per_kg')) {
                $table->decimal('rate_per_kg', 10, 2)->nullable()->after('weight_kg');
            }
            if (!Schema::hasColumn('incomes', 'remarks')) {
                $table->text('remarks')->nullable()->after('amount');
            }
        });

        Schema::table('buffaloes', function (Blueprint $table) {
            if (!Schema::hasColumn('buffaloes', 'sold_date')) {
                $table->date('sold_date')->nullable();
            }
            if (!Schema::hasColumn('buffaloes', 'sale_price')) {
                $table->decimal('sale_price', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('buffaloes', 'buyer_name')) {
                $table->string('buyer_name')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            foreach (['buyer_name', 'weight_kg', 'rate_per_kg', 'remarks'] as $col) {
                if (Schema::hasColumn('incomes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
