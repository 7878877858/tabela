<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buffaloes', function (Blueprint $table) {
            if (!Schema::hasColumn('buffaloes', 'animal_type')) {
                $table->enum('animal_type', ['buffalo', 'cow'])->default('buffalo')->after('tag_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('buffaloes', function (Blueprint $table) {
            if (Schema::hasColumn('buffaloes', 'animal_type')) {
                $table->dropColumn('animal_type');
            }
        });
    }
};
