<?php

namespace Domain\User;

interface UserRepository
{
    /** @throws EmailAlreadyInUse */
    public function create(string $name, string $email, string $passwordHash): void;
}
