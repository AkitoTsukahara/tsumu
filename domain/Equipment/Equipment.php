<?php

declare(strict_types=1);

namespace Domain\Equipment;

use Domain\Equipment\Exceptions\InvalidEquipmentOwnerException;

final readonly class Equipment
{
    public function __construct(
        public int $userId,
        public EquipmentName $name,
        public EquipmentCategory $category,
        public WeightUnit $weightUnit,
        public WeightIncrement $weightIncrement,
    ) {
        if ($this->userId < 1) {
            throw new InvalidEquipmentOwnerException;
        }
    }
}
