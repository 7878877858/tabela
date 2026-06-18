<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    protected array $tables = [
        'daily_report_pregnancies',
        'daily_report_healths',
        'daily_report_vaccinations',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'buffalo_id')) {
                continue;
            }

            $this->dropBuffaloForeignKey($tableName);

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('buffalo_id')
                    ->references('id')
                    ->on('buffaloes')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $this->dropBuffaloForeignKey($tableName);
        }
    }

    protected function dropBuffaloForeignKey(string $tableName): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['buffalo_id']);
            });
        } catch (\Throwable $e) {
            // FK may be missing or already dropped on partial/failed migrate.
        }
    }
};
