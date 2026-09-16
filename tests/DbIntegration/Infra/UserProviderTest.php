<?php

use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Auth;
use Infra\Persistence\Eloquent\Models\User;

test('the auth provider retrieves the infrastructure user created by its factory', function () {
    $user = User::factory()->create();

    $retrieved = Auth::guard('web')->getProvider()->retrieveById($user->getAuthIdentifier());

    expect($retrieved)->toBeInstanceOf(User::class)
        ->and($retrieved->getAuthIdentifier())->toBe($user->getAuthIdentifier());
});

test('the user factory can resolve its infrastructure model directly', function () {
    $user = UserFactory::new()->create();

    expect($user)->toBeInstanceOf(User::class);
    $this->assertModelExists($user);
});
