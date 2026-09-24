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
        Schema::create('exercises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->string('name', 100);
            $table->enum('primary_target', [
                'chest',
                'back',
                'shoulders',
                'biceps',
                'triceps',
                'forearms',
                'quadriceps',
                'hamstrings',
                'glutes',
                'calves',
                'core',
                'full_body',
                'other',
            ]);
            $table->enum('secondary_target', [
                'chest',
                'back',
                'shoulders',
                'biceps',
                'triceps',
                'forearms',
                'quadriceps',
                'hamstrings',
                'glutes',
                'calves',
                'core',
                'full_body',
                'other',
            ])->nullable();
            $table->enum('recording_method', [
                'weight_repetitions',
                'duration',
                'duration_distance',
                'speed_duration',
            ]);
            $table->timestamps();

            $table->index(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
