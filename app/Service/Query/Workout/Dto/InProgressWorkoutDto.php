<?php

declare(strict_types=1);

namespace App\Service\Query\Workout\Dto;

use DateTimeImmutable;

final readonly class InProgressWorkoutDto
{
    public function __construct(
        public string $id,
        public DateTimeImmutable $startedAt,
    ) {}
}
