<?php

declare(strict_types=1);

namespace Domain\Equipment;

use Domain\Equipment\Exceptions\InvalidEquipmentNameException;

final readonly class EquipmentName
{
    public const int MAX_LENGTH = 100;

    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);

        if ($value === '' || mb_strlen($value) > self::MAX_LENGTH) {
            throw new InvalidEquipmentNameException;
        }

        return new self($value);
    }
}
