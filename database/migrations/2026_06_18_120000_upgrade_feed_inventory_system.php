<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feed_stock_transactions') && !Schema::hasTable('feed_transactions')) {
            Schema::rename('feed_stock_transactions', 'feed_transactions');
        }

        Schema::table('feed_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('feed_transactions', 'rate')) {
                $table->decimal('rate', 12, 2)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('feed_transactions', 'total_amount')) {
                $table->decimal('total_amount', 14, 2)->nullable()->after('rate');
            }
            if (!Schema::hasColumn('feed_transactions', 'supplier')) {
                $table->string('supplier')->nullable()->after('total_amount');
            }
        });

        Schema::table('feed_transactions', function (Blueprint $table) {
            $table->index(['feed_id', 'transaction_date'], 'feed_txn_feed_date_idx');
            $table->index(['feed_id', 'direction'], 'feed_txn_feed_direction_idx');
            $table->index('daily_report_id', 'feed_txn_daily_report_idx');
        });

        if (!Schema::hasColumn('feeds', 'min_stock')) {
            Schema::table('feeds', function (Blueprint $table) {
                $table->decimal('min_stock', 10, 2)->default(0)->after('unit');
            });
        }
    }

    public function down(): void
    {
        Schema::table('feed_transactions', function (Blueprint $table) {
            $table->dropIndex('feed_txn_feed_date_idx');
            $table->dropIndex('feed_txn_feed_direction_idx');
            $table->dropIndex('feed_txn_daily_report_idx');

            foreach (['rate', 'total_amount', 'supplier'] as $col) {
                if (Schema::hasColumn('feed_transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasTable('feed_transactions') && !Schema::hasTable('feed_stock_transactions')) {
            Schema::rename('feed_transactions', 'feed_stock_transactions');
        }
    }
};
