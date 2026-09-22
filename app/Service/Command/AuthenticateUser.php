<?php

namespace App\Service\Command;

use Illuminate\Contracts\Auth\StatefulGuard;

final readonly class AuthenticateUser
{
    public function __construct(private StatefulGuard $guard) {}

    public function handle(string $email, string $password): bool
    {
        return $this->guard->attempt([
            'email' => mb_strtolower(trim($email)),
            'password' => $password,
        ]);
    }
}
