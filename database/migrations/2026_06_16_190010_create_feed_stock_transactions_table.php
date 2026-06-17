<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained('feeds')->cascadeOnDelete();
            $table->enum('transaction_type', ['purchase', 'consume', 'adjust', 'return']);
            $table->decimal('quantity', 12, 2); // always positive amount moved
            $table->enum('direction', ['in', 'out']);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->date('transaction_date');
            $table->foreignId('buffalo_id')->nullable()->constrained('buffaloes')->nullOnDelete();
            $table->foreignId('daily_report_id')->nullable()->constrained('daily_reports')->nullOnDelete();
            $table->string('feed_time')->nullable(); // morning / evening
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_stock_transactions');
    }
};
