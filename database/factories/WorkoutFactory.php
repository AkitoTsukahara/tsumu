<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Infra\Persistence\Eloquent\Models\User;
use Infra\Persistence\Eloquent\Models\Workout;

/**
 * @extends Factory<Workout>
 */
class WorkoutFactory extends Factory
{
    /** @var class-string<Workout> */
    protected $model = Workout::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'started_at' => now()->subHour(),
            'completed_at' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => ['user_id' => $user->id]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => ['completed_at' => now()]);
    }
}
