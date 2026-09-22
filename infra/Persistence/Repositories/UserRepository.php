<?php

namespace Infra\Persistence\Repositories;

use Domain\User\Exceptions\EmailAlreadyInUseException;
use Domain\User\UserRepository as UserRepositoryContract;
use Illuminate\Database\UniqueConstraintViolationException;
use Infra\Persistence\Eloquent\Models\User;

final class UserRepository implements UserRepositoryContract
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
            throw new EmailAlreadyInUseException(previous: $exception);
        }
    }
}
