<?php

use Domain\User\EmailAlreadyInUse;
use Domain\User\UserRepository;
use Illuminate\Support\Facades\Hash;
use Infra\Persistence\Eloquent\Models\User;

it('persists a user through the domain repository contract', function () {
    $repository = app(UserRepository::class);
    $passwordHash = Hash::make('secret-password');

    $repository->create('Akito', 'akito@example.com', $passwordHash);

    $user = User::query()->sole();
    expect($user->name)->toBe('Akito')
        ->and($user->email)->toBe('akito@example.com')
        ->and($user->password)->toBe($passwordHash);
});

it('reports the domain error when an email address already exists', function () {
    User::factory()->create(['email' => 'akito@example.com']);

    app(UserRepository::class)->create(
        name: 'Another Akito',
        email: 'akito@example.com',
        passwordHash: Hash::make('secret-password'),
    );
})->throws(EmailAlreadyInUse::class);
