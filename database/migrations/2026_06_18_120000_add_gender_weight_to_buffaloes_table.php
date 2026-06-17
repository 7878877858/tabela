<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buffaloes', function (Blueprint $table) {
            if (!Schema::hasColumn('buffaloes', 'gender')) {
                $table->enum('gender', ['male', 'female'])->nullable()->after('name');
            }
            if (!Schema::hasColumn('buffaloes', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable()->after('gender');
            }
        });
    }

    public function down(): void
    {
        Schema::table('buffaloes', function (Blueprint $table) {
            foreach (['gender', 'weight'] as $col) {
                if (Schema::hasColumn('buffaloes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
