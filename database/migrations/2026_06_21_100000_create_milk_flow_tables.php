<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milk_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 20)->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('milk_distributions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('customer_id')->constrained('milk_customers')->cascadeOnDelete();
            $table->enum('milk_type', ['buffalo', 'cow']);
            $table->decimal('morning_liter', 10, 2)->default(0);
            $table->decimal('evening_liter', 10, 2)->default(0);
            $table->decimal('rate_per_liter', 10, 2)->default(0);
            $table->decimal('total_liter', 10, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['date', 'milk_type']);
        });

        Schema::create('dairy_collections', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('buffalo_liter', 10, 2)->default(0);
            $table->decimal('buffalo_fat', 5, 2)->nullable();
            $table->decimal('buffalo_snf', 5, 2)->nullable();
            $table->decimal('buffalo_amount', 12, 2)->default(0);
            $table->decimal('cow_liter', 10, 2)->default(0);
            $table->decimal('cow_fat', 5, 2)->nullable();
            $table->decimal('cow_snf', 5, 2)->nullable();
            $table->decimal('cow_amount', 12, 2)->default(0);
            $table->string('slip_number')->nullable();
            $table->string('slip_image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('slip_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dairy_collections');
        Schema::dropIfExists('milk_distributions');
        Schema::dropIfExists('milk_customers');
    }
};
