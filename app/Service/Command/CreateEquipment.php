<?php

namespace App\Service\Command;

use Domain\Equipment\Equipment;
use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\EquipmentId;
use Domain\Equipment\EquipmentName;
use Domain\Equipment\EquipmentRepository;
use Domain\Equipment\WeightIncrement;
use Domain\Equipment\WeightUnit;
use Domain\User\UserId;
use Illuminate\Support\Str;

final readonly class CreateEquipment
{
    public function __construct(private EquipmentRepository $equipment) {}

    public function handle(
        UserId $userId,
        string $name,
        EquipmentCategory $category,
        WeightUnit $weightUnit,
        string $weightIncrement,
    ): EquipmentId {
        $equipment = new Equipment(
            id: EquipmentId::fromString((string) Str::uuid7()),
            userId: $userId,
            name: EquipmentName::fromString($name),
            category: $category,
            weightUnit: $weightUnit,
            weightIncrement: WeightIncrement::fromDecimal($weightIncrement),
        );

        $this->equipment->save($equipment);

        return $equipment->id;
    }
}
