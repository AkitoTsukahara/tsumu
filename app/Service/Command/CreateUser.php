<?php

namespace App\Service\Command;

use Domain\User\UserRepository;
use Illuminate\Contracts\Hashing\Hasher;

final readonly class CreateUser
{
    public function __construct(
        private UserRepository $users,
        private Hasher $hasher,
    ) {}

    public function handle(string $name, string $email, string $password): void
    {
        $this->users->create(
            name: trim($name),
            email: mb_strtolower(trim($email)),
            passwordHash: $this->hasher->make($password),
        );
    }
}
