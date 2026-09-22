<?php

declare(strict_types=1);

namespace Domain\User;

use Domain\User\Exceptions\InvalidUserIdException;

final readonly class UserId
{
    private const string UUID_V7_FORMAT = '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = mb_strtolower(trim($value));

        if (preg_match(self::UUID_V7_FORMAT, $value) !== 1) {
            throw new InvalidUserIdException;
        }

        return new self($value);
    }
}
