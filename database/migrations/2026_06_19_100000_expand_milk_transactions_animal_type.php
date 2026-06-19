<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('milk_transactions') || !Schema::hasColumn('milk_transactions', 'animal_type')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE milk_transactions MODIFY animal_type VARCHAR(20) NULL");
        } else {
            Schema::table('milk_transactions', function (Blueprint $table) {
                $table->string('animal_type', 20)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('milk_transactions')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE milk_transactions MODIFY animal_type ENUM('buffalo','cow','mixed') NULL");
        }
    }
};
