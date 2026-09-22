<?php

use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Infra\Persistence\Eloquent\Models\User;

test('認証プロバイダーがFactoryで作成したInfraのユーザーを取得する', function () {
    $user = User::factory()->create();

    $retrieved = Auth::guard('web')->getProvider()->retrieveById($user->getAuthIdentifier());

    expect($retrieved)->toBeInstanceOf(User::class)
        ->and($retrieved->getAuthIdentifier())->toBe($user->getAuthIdentifier())
        ->and(Str::isUuid($user->id, 7))->toBeTrue();
});

test('User FactoryがInfraのモデルを直接解決できる', function () {
    $user = UserFactory::new()->create();

    expect($user)->toBeInstanceOf(User::class);
    $this->assertModelExists($user);
});
