<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_vaccinations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('daily_report_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('buffalo_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('vaccine_name');

            $table->date('vaccination_date')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_vaccinations');
    }
};