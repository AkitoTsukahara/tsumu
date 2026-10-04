<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\Workout;
use Infra\Persistence\Eloquent\Models\WorkoutSet;

/**
 * @extends Factory<WorkoutSet>
 */
class WorkoutSetFactory extends Factory
{
    /** @var class-string<WorkoutSet> */
    protected $model = WorkoutSet::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position' => 1,
            'weight' => '36.00',
            'repetitions' => 10,
            'completed_at' => now(),
        ];
    }

    public function forWorkoutExercise(Workout $workout, Exercise $exercise): static
    {
        return $this->state(fn (): array => [
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);
    }
}
