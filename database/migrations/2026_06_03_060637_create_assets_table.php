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
        Schema::create('assets', function (Blueprint $table) {
        $table->id();

        $table->string('name');
        $table->string('category')->nullable();

        $table->integer('quantity')->default(1);

        $table->date('purchase_date')->nullable();

        $table->decimal('purchase_cost',12,2)->default(0);

        $table->decimal('current_value',12,2)->nullable();

        $table->string('condition')->default('good');

        $table->string('image')->nullable();

        $table->enum('status',['active','sold','scrap'])
            ->default('active');

        $table->text('description')->nullable();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
