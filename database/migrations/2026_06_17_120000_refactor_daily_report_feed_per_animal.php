<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_report_feed', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_report_feed', 'morning_feeds')) {
                $table->json('morning_feeds')->nullable()->after('buffalo_id');
            }
            if (!Schema::hasColumn('daily_report_feed', 'evening_feeds')) {
                $table->json('evening_feeds')->nullable()->after('morning_feeds');
            }
            if (!Schema::hasColumn('daily_report_feed', 'total_feed')) {
                $table->decimal('total_feed', 10, 2)->default(0)->after('evening_feeds');
            }
        });

        $consolidated = $this->collectLegacyRows();

        Schema::table('daily_report_feed', function (Blueprint $table) {
            if (Schema::hasColumn('daily_report_feed', 'feed_id')) {
                $table->dropConstrainedForeignId('feed_id');
            }
            foreach (['feed_name', 'quantity', 'unit', 'feed_time'] as $col) {
                if (Schema::hasColumn('daily_report_feed', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        DB::table('daily_report_feed')->truncate();

        foreach ($consolidated as $item) {
            DB::table('daily_report_feed')->insert($item);
        }

        Schema::table('daily_report_feed', function (Blueprint $table) {
            $table->unique(['daily_report_id', 'buffalo_id'], 'daily_report_feed_report_buffalo_unique');
        });
    }

    protected function collectLegacyRows(): array
    {
        if (!Schema::hasColumn('daily_report_feed', 'feed_time')) {
            return [];
        }

        $rows = DB::table('daily_report_feed')->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return [];
        }

        $grouped = [];

        foreach ($rows as $row) {
            if (!$row->buffalo_id) {
                continue;
            }

            $key = $row->daily_report_id . ':' . $row->buffalo_id;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'daily_report_id' => $row->daily_report_id,
                    'buffalo_id'      => $row->buffalo_id,
                    'morning'         => [],
                    'evening'         => [],
                    'created_at'      => $row->created_at,
                    'updated_at'      => $row->updated_at,
                ];
            }

            $feedKey = (string) ($row->feed_id ?: $row->feed_name);
            $qty = (float) $row->quantity;

            if ($row->feed_time === 'evening') {
                $grouped[$key]['evening'][$feedKey] = ($grouped[$key]['evening'][$feedKey] ?? 0) + $qty;
            } else {
                $grouped[$key]['morning'][$feedKey] = ($grouped[$key]['morning'][$feedKey] ?? 0) + $qty;
            }
        }

        $result = [];

        foreach ($grouped as $item) {
            $total = array_sum($item['morning']) + array_sum($item['evening']);

            $result[] = [
                'daily_report_id' => $item['daily_report_id'],
                'buffalo_id'      => $item['buffalo_id'],
                'morning_feeds'   => json_encode($item['morning']),
                'evening_feeds'   => json_encode($item['evening']),
                'total_feed'      => $total,
                'created_at'      => $item['created_at'],
                'updated_at'      => $item['updated_at'],
            ];
        }

        return $result;
    }

    public function down(): void
    {
        Schema::table('daily_report_feed', function (Blueprint $table) {
            $table->dropUnique('daily_report_feed_report_buffalo_unique');
        });

        Schema::table('daily_report_feed', function (Blueprint $table) {
            $table->string('feed_name')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit')->default('kg');
            $table->enum('feed_time', ['morning', 'evening'])->nullable();
            $table->foreignId('feed_id')->nullable()->constrained('feeds')->nullOnDelete();
            $table->dropColumn(['morning_feeds', 'evening_feeds', 'total_feed']);
        });
    }
};
