<?php

declare(strict_types=1);

namespace Domain\Equipment;

use Domain\User\UserId;

interface EquipmentRepository
{
    public function save(Equipment $equipment): void;

    public function update(Equipment $equipment): bool;

    public function findOwnedBy(EquipmentId $equipmentId, UserId $userId): ?Equipment;
}
