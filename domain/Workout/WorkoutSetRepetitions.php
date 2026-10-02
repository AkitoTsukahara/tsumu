<?php

declare(strict_types=1);

namespace Domain\Workout;

use Domain\Workout\Exceptions\InvalidWorkoutSetRepetitionsException;

final readonly class WorkoutSetRepetitions
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1 || $value > 999) {
            throw new InvalidWorkoutSetRepetitionsException;
        }

        return new self($value);
    }
}
