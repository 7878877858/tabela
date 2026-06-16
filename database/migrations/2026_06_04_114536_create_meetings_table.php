<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {

            $table->id();

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('agenda')->nullable();

            $table->date('meeting_date');

            $table->time('start_time');
            $table->time('end_time');

            $table->string('location')->nullable();

            $table->string('meeting_link')->nullable();

            $table->enum('status', [
                'scheduled',
                'ongoing',
                'completed',
                'cancelled'
            ])->default('scheduled');

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};