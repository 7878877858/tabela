<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
        $table->id();

        $table->date('report_date');
        $table->string('shift')->nullable();

        $table->integer('total_animals')->default(0);
        $table->decimal('total_milk',10,2)->default(0);

        $table->integer('present_staff')->default(0);
        $table->integer('absent_staff')->default(0);

        $table->text('notes')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
