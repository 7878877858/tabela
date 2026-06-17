<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('buffaloes', 'animal_type')) {
            Schema::table('buffaloes', function (Blueprint $table) {
                $table->string('animal_type', 20)->default('buffalo')->after('tag_number');
            });

            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE buffaloes MODIFY animal_type VARCHAR(20) NOT NULL DEFAULT 'buffalo'");
        } else {
            Schema::table('buffaloes', function (Blueprint $table) {
                $table->string('animal_type', 20)->default('buffalo')->change();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('buffaloes', 'animal_type')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE buffaloes MODIFY animal_type ENUM('buffalo','cow') NOT NULL DEFAULT 'buffalo'");
        }
    }
};
