<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('milk_distributions', function (Blueprint $table) {
            if (!Schema::hasColumn('milk_distributions', 'daily_report_id')) {
                $table->foreignId('daily_report_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('daily_reports')
                    ->nullOnDelete();
            }
        });

        Schema::table('dairy_collections', function (Blueprint $table) {
            if (!Schema::hasColumn('dairy_collections', 'daily_report_id')) {
                $table->foreignId('daily_report_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('daily_reports')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('milk_distributions', function (Blueprint $table) {
            if (Schema::hasColumn('milk_distributions', 'daily_report_id')) {
                $table->dropConstrainedForeignId('daily_report_id');
            }
        });

        Schema::table('dairy_collections', function (Blueprint $table) {
            if (Schema::hasColumn('dairy_collections', 'daily_report_id')) {
                $table->dropConstrainedForeignId('daily_report_id');
            }
        });
    }
};
