<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milk_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction_type', ['production', 'sale', 'wastage', 'adjust']);
            $table->decimal('liters', 12, 2); // always positive amount moved
            $table->enum('direction', ['in', 'out']);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->date('transaction_date');
            $table->enum('animal_type', ['buffalo', 'cow', 'mixed'])->nullable();
            $table->foreignId('buffalo_id')->nullable()->constrained('buffaloes')->nullOnDelete();
            $table->foreignId('milk_entry_id')->nullable()->constrained('milk_entries')->nullOnDelete();
            $table->foreignId('milk_sale_id')->nullable()->constrained('milk_sales')->nullOnDelete();
            $table->foreignId('daily_report_id')->nullable()->constrained('daily_reports')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milk_transactions');
    }
};
