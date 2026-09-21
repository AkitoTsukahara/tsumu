<?php

namespace Domain\User;

use Domain\User\Exceptions\EmailAlreadyInUseException;

interface UserRepository
{
    /** @throws EmailAlreadyInUseException */
    public function create(string $name, string $email, string $passwordHash): void;
}
