<?php

declare(strict_types=1);

namespace Infra\Persistence\Queries;

use App\Service\Query\Equipment\Dto\EquipmentListItemCollection;
use App\Service\Query\Equipment\Dto\EquipmentListItemDto;
use App\Service\Query\Equipment\EquipmentListQuery as EquipmentListQueryContract;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Equipment;

final class EquipmentListQuery implements EquipmentListQueryContract
{
    public function forUser(UserId $userId): EquipmentListItemCollection
    {
        $items = Equipment::query()
            ->where('user_id', $userId->value)
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'category', 'weight_unit', 'weight_increment'])
            ->map(fn (Equipment $equipment): EquipmentListItemDto => new EquipmentListItemDto(
                id: $equipment->id,
                name: $equipment->name,
                category: $equipment->category->value,
                weightUnit: $equipment->weight_unit->value,
                weightIncrement: $equipment->weight_increment,
            ))
            ->all();

        return new EquipmentListItemCollection($items);
    }
}
