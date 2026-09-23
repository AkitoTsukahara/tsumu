<?php

declare(strict_types=1);

namespace Infra\Persistence\Repositories;

use Domain\Equipment\Equipment;
use Domain\Equipment\EquipmentId;
use Domain\Equipment\EquipmentRepository as EquipmentRepositoryContract;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Equipment as EquipmentModel;
use Infra\Persistence\Mappers\EquipmentMapper;

final class EquipmentRepository implements EquipmentRepositoryContract
{
    public function __construct(private readonly EquipmentMapper $mapper) {}

    public function save(Equipment $equipment): void
    {
        EquipmentModel::query()->create($this->mapper->toPersistence($equipment));
    }

    public function findOwnedBy(EquipmentId $equipmentId, UserId $userId): ?Equipment
    {
        $model = EquipmentModel::query()
            ->whereKey($equipmentId->value)
            ->where('user_id', $userId->value)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }
}
