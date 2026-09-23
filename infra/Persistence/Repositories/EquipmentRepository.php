<?php

declare(strict_types=1);

namespace Infra\Persistence\Repositories;

use Domain\Equipment\Equipment;
use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\EquipmentId;
use Domain\Equipment\EquipmentName;
use Domain\Equipment\EquipmentRepository as EquipmentRepositoryContract;
use Domain\Equipment\WeightIncrement;
use Domain\Equipment\WeightUnit;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Equipment as EquipmentModel;

final class EquipmentRepository implements EquipmentRepositoryContract
{
    public function save(Equipment $equipment): void
    {
        EquipmentModel::query()->create([
            'id' => $equipment->id->value,
            'user_id' => $equipment->userId->value,
            'name' => $equipment->name->value,
            'category' => $equipment->category,
            'weight_unit' => $equipment->weightUnit,
            'weight_increment' => $equipment->weightIncrement->toDecimal(),
        ]);
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

        return new Equipment(
            id: EquipmentId::fromString($model->id),
            userId: UserId::fromString($model->user_id),
            name: EquipmentName::fromString($model->name),
            category: EquipmentCategory::from($model->category->value),
            weightUnit: WeightUnit::from($model->weight_unit->value),
            weightIncrement: WeightIncrement::fromDecimal($model->weight_increment),
        );
    }
}
