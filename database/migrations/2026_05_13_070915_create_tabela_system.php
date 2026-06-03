<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buffalo table
        Schema::create('buffaloes', function (Blueprint $table) {
            $table->id();
            $table->string('tag_number')->unique(); // ટેગ નંબર
            $table->string('name')->nullable();      // નામ
            $table->date('dob')->nullable();         // જન્મ તારીખ
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->enum('status', ['active', 'sold', 'dead'])->default('active');
            $table->enum('lactation_status', ['lactating', 'dry', 'pregnant'])->default('dry');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Daily milk entries
        Schema::create('milk_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buffalo_id')->constrained('buffaloes')->onDelete('cascade');
            $table->date('entry_date');
            $table->decimal('morning_liters', 8, 2)->default(0);
            $table->decimal('evening_liters', 8, 2)->default(0);
            $table->decimal('total_liters', 8, 2)->storedAs('morning_liters + evening_liters');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['buffalo_id', 'entry_date']);
        });

        // Milk sales
        Schema::create('milk_sales', function (Blueprint $table) {
            $table->id();
            $table->date('sale_date');
            $table->decimal('liters_sold', 8, 2);
            $table->decimal('price_per_liter', 8, 2);
            $table->decimal('total_amount', 10, 2)->storedAs('liters_sold * price_per_liter');
            $table->string('buyer_name')->nullable();
            $table->enum('payment_status', ['paid', 'pending'])->default('paid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Expenses / Kharch
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('expense_date');
            $table->enum('category', [
                'feed',       // ચારો / ઘાસ
                'medicine',   // દવા
                'labour',     // મજૂરી
                'equipment',  // સાધન
                'veterinary', // પશુ ડૉક્ટર
                'other'       // અન્ય
            ]);
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->foreignId('buffalo_id')->nullable()->constrained('buffaloes')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Employees
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('employee_type'); // e.g. Milker, Cleaner, Manager
            $table->string('mobile')->nullable();
            $table->date('join_date');
            $table->decimal('monthly_salary', 10, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Salary payments
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('payment_date');
            $table->integer('month'); // 1-12
            $table->integer('year');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['paid', 'pending'])->default('paid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Settings (theme color etc)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('milk_sales');
        Schema::dropIfExists('milk_entries');
        Schema::dropIfExists('buffaloes');
        Schema::dropIfExists('settings');
    }
};