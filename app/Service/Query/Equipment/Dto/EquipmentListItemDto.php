<?php

declare(strict_types=1);

namespace App\Service\Query\Equipment\Dto;

final readonly class EquipmentListItemDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $category,
        public string $weightUnit,
        public string $weightIncrement,
    ) {}
}
