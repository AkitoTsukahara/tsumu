<?php

declare(strict_types=1);

namespace Domain\Equipment;

use Domain\Equipment\Exceptions\InvalidWeightIncrementException;

final readonly class WeightIncrement
{
    private const string FORMAT = '/^(?:0|[1-9]\d{0,2})(?:\.\d{1,2})?$/';

    private function __construct(private int $hundredths) {}

    public static function fromDecimal(string $value): self
    {
        $value = trim($value);

        if (preg_match(self::FORMAT, $value) !== 1) {
            throw new InvalidWeightIncrementException;
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $hundredths = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        if ($hundredths === 0) {
            throw new InvalidWeightIncrementException;
        }

        return new self($hundredths);
    }

    public function toDecimal(): string
    {
        return sprintf('%d.%02d', intdiv($this->hundredths, 100), $this->hundredths % 100);
    }
}
