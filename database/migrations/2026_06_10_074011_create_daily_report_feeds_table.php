<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_feed', function (Blueprint $table) {
            $table->id();

            $table->foreignId('daily_report_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('feed_name');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit')->default('kg');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_feed');
    }
};