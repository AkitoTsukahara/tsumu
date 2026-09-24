<?php

namespace Database\Factories;

use Domain\Exercise\BodyPart;
use Domain\Exercise\RecordingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\User;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    /** @var class-string<Exercise> */
    protected $model = Exercise::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'equipment_id' => null,
            'name' => fake()->words(2, true),
            'primary_target' => BodyPart::Quadriceps,
            'secondary_target' => BodyPart::Glutes,
            'recording_method' => RecordingMethod::WeightAndRepetitions,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => ['user_id' => $user->id]);
    }

    public function forEquipment(Equipment $equipment): static
    {
        return $this->state(fn (): array => [
            'user_id' => $equipment->user_id,
            'equipment_id' => $equipment->id,
        ]);
    }
}
