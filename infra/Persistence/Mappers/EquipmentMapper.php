<?php

declare(strict_types=1);

namespace Infra\Persistence\Mappers;

use Domain\Equipment\Equipment;
use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\EquipmentId;
use Domain\Equipment\EquipmentName;
use Domain\Equipment\WeightIncrement;
use Domain\Equipment\WeightUnit;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Equipment as EquipmentModel;

final class EquipmentMapper
{
    /**
     * @return array{
     *     id: string,
     *     user_id: string,
     *     name: string,
     *     category: EquipmentCategory,
     *     weight_unit: WeightUnit,
     *     weight_increment: string
     * }
     */
    public function toPersistence(Equipment $equipment): array
    {
        return [
            'id' => $equipment->id->value,
            'user_id' => $equipment->userId->value,
            'name' => $equipment->name->value,
            'category' => $equipment->category,
            'weight_unit' => $equipment->weightUnit,
            'weight_increment' => $equipment->weightIncrement->toDecimal(),
        ];
    }

    public function toDomain(EquipmentModel $model): Equipment
    {
        return new Equipment(
            id: EquipmentId::fromString($model->id),
            userId: UserId::fromString($model->user_id),
            name: EquipmentName::fromString($model->name),
            category: $model->category,
            weightUnit: $model->weight_unit,
            weightIncrement: WeightIncrement::fromDecimal($model->weight_increment),
        );
    }
}
