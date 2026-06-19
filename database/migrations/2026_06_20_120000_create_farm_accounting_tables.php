<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('daily_report_expenses') && !Schema::hasColumn('daily_report_expenses', 'expense_type')) {
            Schema::table('daily_report_expenses', function (Blueprint $table) {
                $table->string('expense_type', 30)->default('other_daily')->after('daily_report_id');
            });
        }

        Schema::create('utility_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_type', 30);
            $table->decimal('amount', 12, 2);
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->string('insurance_type', 30);
            $table->string('policy_number')->nullable();
            $table->decimal('premium_amount', 12, 2);
            $table->date('start_date');
            $table->date('expiry_date');
            $table->string('status', 20)->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('farm_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_name');
            $table->string('bank_name')->nullable();
            $table->decimal('loan_amount', 14, 2);
            $table->decimal('emi_amount', 12, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('outstanding_balance', 14, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('farm_other_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('feed_purchases', function (Blueprint $table) {
            $table->id();
            $table->date('purchase_date');
            $table->string('feed_type', 30);
            $table->decimal('quantity', 12, 2);
            $table->string('unit', 20)->default('kg');
            $table->decimal('rate', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->string('supplier')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('feed_id')->nullable()->constrained('feeds')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('animal_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type', 20);
            $table->foreignId('buffalo_id')->constrained('buffaloes')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('counterparty_name')->nullable();
            $table->date('transaction_date');
            $table->text('remarks')->nullable();
            $table->foreignId('income_id')->nullable()->constrained('incomes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_transactions');
        Schema::dropIfExists('feed_purchases');
        Schema::dropIfExists('farm_other_expenses');
        Schema::dropIfExists('farm_loans');
        Schema::dropIfExists('insurance_policies');
        Schema::dropIfExists('utility_bills');

        if (Schema::hasColumn('daily_report_expenses', 'expense_type')) {
            Schema::table('daily_report_expenses', function (Blueprint $table) {
                $table->dropColumn('expense_type');
            });
        }
    }
};
