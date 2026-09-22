<?php

declare(strict_types=1);

namespace Domain\Shared;

use Domain\Shared\Exceptions\InvalidUuidV7IdException;

abstract readonly class UuidV7Id
{
    private const string FORMAT = '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    final protected function __construct(public string $value) {}

    final public static function fromString(string $value): static
    {
        $value = mb_strtolower(trim($value));

        if (preg_match(self::FORMAT, $value) !== 1) {
            throw new InvalidUuidV7IdException;
        }

        return new static($value);
    }
}
