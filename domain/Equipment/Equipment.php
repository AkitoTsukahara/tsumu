<?php

declare(strict_types=1);

namespace Domain\Equipment;

use Domain\User\UserId;

final readonly class Equipment
{
    public function __construct(
        public UserId $userId,
        public EquipmentName $name,
        public EquipmentCategory $category,
        public WeightUnit $weightUnit,
        public WeightIncrement $weightIncrement,
    ) {}
}
