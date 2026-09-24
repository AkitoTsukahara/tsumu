<?php

namespace App\Livewire\Forms\Dto;

use Domain\Equipment\EquipmentId;
use Domain\Exercise\BodyPart;

final readonly class ValidatedExerciseInputDto
{
    public function __construct(
        public string $name,
        public ?EquipmentId $equipmentId,
        public BodyPart $primaryTarget,
        public ?BodyPart $secondaryTarget,
    ) {}
}
