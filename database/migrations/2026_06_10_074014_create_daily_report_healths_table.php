<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_healths', function (Blueprint $table) {
            $table->id();

            $table->foreignId('daily_report_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('buffalo_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('health_issue');
            $table->text('treatment')->nullable();
            $table->decimal('medicine_cost', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_healths');
    }
};