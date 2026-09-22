<?php

declare(strict_types=1);

namespace Domain\User;

use Domain\Shared\UuidV7Id;
use Domain\User\Exceptions\InvalidUserIdException;
use DomainException;

final readonly class UserId extends UuidV7Id
{
    protected static function invalidIdException(): DomainException
    {
        return new InvalidUserIdException;
    }
}
