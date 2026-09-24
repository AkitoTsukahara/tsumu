<?php

declare(strict_types=1);

namespace App\Service\Query\Exercise\Dto;

final readonly class ExerciseListItemDto
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $equipmentName,
        public string $primaryTarget,
        public ?string $secondaryTarget,
        public string $recordingMethod,
    ) {}
}
