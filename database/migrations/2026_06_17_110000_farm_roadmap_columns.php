<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buffaloes', function (Blueprint $table) {
            if (!Schema::hasColumn('buffaloes', 'heat_date')) {
                $table->date('heat_date')->nullable()->after('notes');
                $table->date('ai_date')->nullable();
                $table->date('pregnancy_check_date')->nullable();
                $table->date('expected_delivery_date')->nullable();
                $table->date('birth_date')->nullable();
                $table->string('calf_tag_number')->nullable();
                $table->enum('calf_gender', ['male', 'female'])->nullable();
                $table->decimal('calf_weight', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('buffaloes', 'mother_buffalo_id')) {
                $table->foreignId('mother_buffalo_id')->nullable()->after('animal_type')
                    ->constrained('buffaloes')->nullOnDelete();
            }
            if (!Schema::hasColumn('buffaloes', 'sold_date')) {
                $table->date('sold_date')->nullable();
                $table->decimal('sale_price', 12, 2)->nullable();
                $table->string('buyer_name')->nullable();
                $table->string('sold_reason')->nullable();
            }
        });

        Schema::table('feeds', function (Blueprint $table) {
            if (!Schema::hasColumn('feeds', 'min_stock')) {
                $table->decimal('min_stock', 10, 2)->default(0)->after('volume');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('manager')->after('email');
            }
        });

        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'buffalo_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreignId('buffalo_id')->nullable()->after('id')
                    ->constrained('buffaloes')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('buffaloes', function (Blueprint $table) {
            $cols = [
                'heat_date', 'ai_date', 'pregnancy_check_date', 'expected_delivery_date',
                'birth_date', 'calf_tag_number', 'calf_gender', 'calf_weight',
                'mother_buffalo_id', 'sold_date', 'sale_price', 'buyer_name', 'sold_reason',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('buffaloes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasColumn('feeds', 'min_stock')) {
            Schema::table('feeds', fn (Blueprint $t) => $t->dropColumn('min_stock'));
        }

        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
        }

        if (Schema::hasColumn('tasks', 'buffalo_id')) {
            Schema::table('tasks', fn (Blueprint $t) => $t->dropConstrainedForeignId('buffalo_id'));
        }
    }
};
