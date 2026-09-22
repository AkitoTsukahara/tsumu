<?php

use App\Service\Command\AuthenticateUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Infra\Persistence\Eloquent\Models\User;

uses(RefreshDatabase::class);

it('正しい認証情報でユーザーを認証する', function () {
    $user = User::factory()->create([
        'email' => 'akito@example.com',
        'password' => Hash::make('secret-password'),
    ]);

    $authenticated = app(AuthenticateUser::class)->handle(
        email: ' AKITO@example.com ',
        password: 'secret-password',
    );

    expect($authenticated)->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

it('誤った認証情報を拒否する', function (string $email, string $password) {
    User::factory()->create([
        'email' => 'akito@example.com',
        'password' => Hash::make('secret-password'),
    ]);

    $authenticated = app(AuthenticateUser::class)->handle($email, $password);

    expect($authenticated)->toBeFalse();
    $this->assertGuest();
})->with([
    'パスワードが不一致' => ['akito@example.com', 'wrong-password'],
    'メールアドレスが存在しない' => ['nobody@example.com', 'secret-password'],
]);
