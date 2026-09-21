<?php

namespace Infra\Persistence\Eloquent;

use Domain\User\EmailAlreadyInUse;
use Domain\User\UserRepository;
use Illuminate\Database\UniqueConstraintViolationException;
use Infra\Persistence\Eloquent\Models\User;

final class EloquentUserRepository implements UserRepository
{
    public function create(string $name, string $email, string $passwordHash): void
    {
        try {
            User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $passwordHash,
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            throw new EmailAlreadyInUse(previous: $exception);
        }
    }
}
