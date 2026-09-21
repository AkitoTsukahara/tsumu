<?php

use Domain\User\Exceptions\EmailAlreadyInUseException;
use Domain\User\UserRepository;
use Illuminate\Support\Facades\Hash;
use Infra\Persistence\Eloquent\Models\User;

it('DomainのRepository契約を通してユーザーを保存する', function () {
    $repository = app(UserRepository::class);
    $passwordHash = Hash::make('secret-password');

    $repository->create('Akito', 'akito@example.com', $passwordHash);

    $user = User::query()->sole();
    expect($user->name)->toBe('Akito')
        ->and($user->email)->toBe('akito@example.com')
        ->and($user->password)->toBe($passwordHash);
});

it('メールアドレスが重複した場合にDomain例外を通知する', function () {
    User::factory()->create(['email' => 'akito@example.com']);

    app(UserRepository::class)->create(
        name: 'Another Akito',
        email: 'akito@example.com',
        passwordHash: Hash::make('secret-password'),
    );
})->throws(EmailAlreadyInUseException::class);
