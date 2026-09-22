<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\Persistence\Eloquent\Models\User;

uses(RefreshDatabase::class);

test('ログインしてTodayを表示しログアウトできる', function () {
    $user = User::factory()->create();

    visit('/login')
        ->resize(360, 800)
        ->type('email', $user->email)
        ->type('password', 'password')
        ->press('button[type="submit"]')
        ->assertPathIs('/')
        ->assertSee('今日も積みましょう')
        ->press('ログアウト')
        ->assertPathIs('/login')
        ->assertSee('ログイン')
        ->assertNoJavascriptErrors();
});
