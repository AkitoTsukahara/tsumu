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

final readonly class UpdateEquipment
{
    public function __construct(private EquipmentRepository $equipment) {}

    public function handle(
        EquipmentId $equipmentId,
        UserId $userId,
        string $name,
        EquipmentCategory $category,
        WeightUnit $weightUnit,
        string $weightIncrement,
    ): bool {
        $equipment = $this->equipment->findOwnedBy($equipmentId, $userId);

        if ($equipment === null) {
            return false;
        }

        return $this->equipment->update(new Equipment(
            id: $equipment->id,
            userId: $equipment->userId,
            name: EquipmentName::fromString($name),
            category: $category,
            weightUnit: $weightUnit,
            weightIncrement: WeightIncrement::fromDecimal($weightIncrement),
        ));
    }
}
