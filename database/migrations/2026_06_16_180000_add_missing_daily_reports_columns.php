<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_reports', 'report_number')) {
                $table->string('report_number')->nullable();
            }

            if (!Schema::hasColumn('daily_reports', 'reporter')) {
                $table->string('reporter')->nullable();
            }

            // Cleaning - cowshed
            if (!Schema::hasColumn('daily_reports', 'clean_cowshed')) {
                $table->boolean('clean_cowshed')->default(false);
            }
            if (!Schema::hasColumn('daily_reports', 'clean_cowshed_by')) {
                $table->unsignedBigInteger('clean_cowshed_by')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'clean_cowshed_note')) {
                $table->text('clean_cowshed_note')->nullable();
            }

            // Cleaning - milk room
            if (!Schema::hasColumn('daily_reports', 'clean_milk_room')) {
                $table->boolean('clean_milk_room')->default(false);
            }
            if (!Schema::hasColumn('daily_reports', 'clean_milk_room_by')) {
                $table->unsignedBigInteger('clean_milk_room_by')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'clean_milk_room_note')) {
                $table->text('clean_milk_room_note')->nullable();
            }

            // Cleaning - store
            if (!Schema::hasColumn('daily_reports', 'clean_store')) {
                $table->boolean('clean_store')->default(false);
            }
            if (!Schema::hasColumn('daily_reports', 'clean_store_by')) {
                $table->unsignedBigInteger('clean_store_by')->nullable();
            }
            if (!Schema::hasColumn('daily_reports', 'clean_store_note')) {
                $table->text('clean_store_note')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            if (Schema::hasColumn('daily_reports', 'report_number')) {
                $table->dropColumn('report_number');
            }
            if (Schema::hasColumn('daily_reports', 'reporter')) {
                $table->dropColumn('reporter');
            }

            if (Schema::hasColumn('daily_reports', 'clean_cowshed')) {
                $table->dropColumn('clean_cowshed');
            }
            if (Schema::hasColumn('daily_reports', 'clean_cowshed_by')) {
                $table->dropColumn('clean_cowshed_by');
            }
            if (Schema::hasColumn('daily_reports', 'clean_cowshed_note')) {
                $table->dropColumn('clean_cowshed_note');
            }

            if (Schema::hasColumn('daily_reports', 'clean_milk_room')) {
                $table->dropColumn('clean_milk_room');
            }
            if (Schema::hasColumn('daily_reports', 'clean_milk_room_by')) {
                $table->dropColumn('clean_milk_room_by');
            }
            if (Schema::hasColumn('daily_reports', 'clean_milk_room_note')) {
                $table->dropColumn('clean_milk_room_note');
            }

            if (Schema::hasColumn('daily_reports', 'clean_store')) {
                $table->dropColumn('clean_store');
            }
            if (Schema::hasColumn('daily_reports', 'clean_store_by')) {
                $table->dropColumn('clean_store_by');
            }
            if (Schema::hasColumn('daily_reports', 'clean_store_note')) {
                $table->dropColumn('clean_store_note');
            }
        });
    }
};

