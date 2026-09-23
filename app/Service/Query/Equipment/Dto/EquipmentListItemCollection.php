<?php

declare(strict_types=1);

namespace App\Service\Query\Equipment\Dto;

use App\Service\Query\Dto\TypedList;

/** @extends TypedList<EquipmentListItemDto> */
final readonly class EquipmentListItemCollection extends TypedList
{
    protected static function itemType(): string
    {
        return EquipmentListItemDto::class;
    }
}
