

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_pregnancies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('daily_report_id')
                ->constrained('daily_reports')
                ->cascadeOnDelete();

            $table->foreignId('buffalo_id')
                ->constrained('buffaloes')
                ->cascadeOnDelete();

            $table->date('checkup_date')->nullable();

            $table->enum('status', [
                'pregnant',
                'not_pregnant',
                'suspected'
            ]);

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_pregnancies');
    }
};