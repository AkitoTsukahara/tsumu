<?php

namespace App\Service\Query;

final readonly class EquipmentListItem
{
    public function __construct(
        public string $id,
        public string $name,
        public string $category,
        public string $weightUnit,
        public string $weightIncrement,
    ) {}
}
