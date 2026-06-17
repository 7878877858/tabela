<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->foreignId('buffalo_id')->constrained('buffaloes')->cascadeOnDelete();
            $table->foreignId('feed_id')->constrained('feeds')->cascadeOnDelete();
            $table->date('entry_date');
            $table->enum('feed_time', ['morning', 'evening']);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['entry_date', 'buffalo_id']);
            $table->index(['daily_report_id', 'buffalo_id']);
            $table->unique(['daily_report_id', 'buffalo_id', 'feed_id', 'feed_time'], 'feed_entries_report_animal_feed_time_unique');
        });

        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->foreignId('buffalo_id')->constrained('buffaloes')->cascadeOnDelete();
            $table->date('record_date');
            $table->string('health_issue');
            $table->text('treatment')->nullable();
            $table->decimal('medicine_cost', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['record_date', 'buffalo_id']);
            $table->index('daily_report_id');
        });

        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->foreignId('buffalo_id')->constrained('buffaloes')->cascadeOnDelete();
            $table->string('vaccine_name');
            $table->date('vaccination_date');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['vaccination_date', 'buffalo_id']);
            $table->index('daily_report_id');
        });

        Schema::table('milk_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('milk_entries', 'daily_report_id')) {
                $table->foreignId('daily_report_id')
                    ->nullable()
                    ->after('buffalo_id')
                    ->constrained('daily_reports')
                    ->nullOnDelete();
                $table->index('daily_report_id');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'daily_report_id')) {
                $table->foreignId('daily_report_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('daily_reports')
                    ->cascadeOnDelete();
                $table->index('daily_report_id');
            }
        });

        Schema::table('incomes', function (Blueprint $table) {
            if (!Schema::hasColumn('incomes', 'daily_report_id')) {
                $table->foreignId('daily_report_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('daily_reports')
                    ->cascadeOnDelete();
                $table->index('daily_report_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            if (Schema::hasColumn('incomes', 'daily_report_id')) {
                $table->dropConstrainedForeignId('daily_report_id');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'daily_report_id')) {
                $table->dropConstrainedForeignId('daily_report_id');
            }
        });

        Schema::table('milk_entries', function (Blueprint $table) {
            if (Schema::hasColumn('milk_entries', 'daily_report_id')) {
                $table->dropConstrainedForeignId('daily_report_id');
            }
        });

        Schema::dropIfExists('vaccination_records');
        Schema::dropIfExists('health_records');
        Schema::dropIfExists('feed_entries');
    }
};
