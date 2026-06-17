<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_report_feed', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_report_feed', 'feed_id')) {
                $table->foreignId('feed_id')->nullable()->constrained('feeds')->nullOnDelete();
            }

            if (!Schema::hasColumn('daily_report_feed', 'buffalo_id')) {
                $table->foreignId('buffalo_id')->nullable()->constrained('buffaloes')->nullOnDelete();
            }

            if (!Schema::hasColumn('daily_report_feed', 'feed_time')) {
                $table->enum('feed_time', ['morning', 'evening'])->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_report_feed', function (Blueprint $table) {
            if (Schema::hasColumn('daily_report_feed', 'feed_time')) {
                $table->dropColumn('feed_time');
            }

            if (Schema::hasColumn('daily_report_feed', 'buffalo_id')) {
                $table->dropConstrainedForeignId('buffalo_id');
            }

            if (Schema::hasColumn('daily_report_feed', 'feed_id')) {
                $table->dropConstrainedForeignId('feed_id');
            }
        });
    }
};

