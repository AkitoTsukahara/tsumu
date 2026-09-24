<?php

declare(strict_types=1);

namespace Domain\Exercise;

use Domain\Exercise\Exceptions\InvalidExerciseNameException;

final readonly class ExerciseName
{
    public const int MAX_LENGTH = 100;

    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);

        if ($value === '' || mb_strlen($value) > self::MAX_LENGTH) {
            throw new InvalidExerciseNameException;
        }

        return new self($value);
    }
}
