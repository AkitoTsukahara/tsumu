<?php

namespace App\Livewire\Forms\Dto;

use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\WeightUnit;

final readonly class ValidatedEquipmentInputDto
{
    public function __construct(
        public string $name,
        public EquipmentCategory $category,
        public WeightUnit $weightUnit,
        public string $weightIncrement,
    ) {}
}
