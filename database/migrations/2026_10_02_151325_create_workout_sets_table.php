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
        Schema::create('workout_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workout_id');
            $table->foreignUuid('exercise_id');
            $table->unsignedSmallInteger('position');
            $table->decimal('weight', total: 6, places: 2);
            $table->unsignedSmallInteger('repetitions');
            $table->timestampTz('completed_at');
            $table->timestamps();

            $table->foreign(['workout_id', 'exercise_id'])
                ->references(['workout_id', 'exercise_id'])
                ->on('workout_exercises')
                ->cascadeOnDelete();
            $table->unique(['workout_id', 'exercise_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_sets');
    }
};
