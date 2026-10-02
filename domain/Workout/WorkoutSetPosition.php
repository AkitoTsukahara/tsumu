<?php

declare(strict_types=1);

namespace Domain\Workout;

use Domain\Workout\Exceptions\InvalidWorkoutSetPositionException;

final readonly class WorkoutSetPosition
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1 || $value > 65_535) {
            throw new InvalidWorkoutSetPositionException;
        }

        return new self($value);
    }
}
