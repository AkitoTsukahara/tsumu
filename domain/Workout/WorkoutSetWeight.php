<?php

declare(strict_types=1);

namespace Domain\Workout;

use Domain\Workout\Exceptions\InvalidWorkoutSetWeightException;

final readonly class WorkoutSetWeight
{
    private const string FORMAT = '/^(?:0|[1-9]\d{0,3})(?:\.\d{1,2})?$/';

    private function __construct(private int $hundredths) {}

    public static function fromDecimal(string $value): self
    {
        $value = trim($value);

        if (preg_match(self::FORMAT, $value) !== 1) {
            throw new InvalidWorkoutSetWeightException;
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return new self(
            ((int) $whole * 100) + (int) str_pad($fraction, 2, '0'),
        );
    }

    public function toDecimal(): string
    {
        return sprintf('%d.%02d', intdiv($this->hundredths, 100), $this->hundredths % 100);
    }
}
