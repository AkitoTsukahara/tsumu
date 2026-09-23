<?php

namespace Database\Factories;

use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\WeightUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\User;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    /** @var class-string<Equipment> */
    protected $model = Equipment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'category' => EquipmentCategory::Machine,
            'weight_unit' => WeightUnit::Kilogram,
            'weight_increment' => '5.00',
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => ['user_id' => $user->id]);
    }
}
