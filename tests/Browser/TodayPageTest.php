<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\Persistence\Eloquent\Models\User;

uses(RefreshDatabase::class);

test('ログインしてTodayから機材を登録しログアウトできる', function () {
    $user = User::factory()->create();

    visit('/login')
        ->resize(360, 800)
        ->type('email', $user->email)
        ->type('password', 'password')
        ->press('button[type="submit"]')
        ->assertPathIs('/')
        ->assertSee('今日も積みましょう')
        ->click('機材を管理')
        ->assertPathIs('/settings/equipment')
        ->assertSee('機材はまだありません')
        ->type('name', 'レッグプレス')
        ->type('weight_increment', '9')
        ->press('登録する')
        ->assertSee('機材を登録しました。')
        ->assertSee('レッグプレス')
        ->assertSee('9.00 kg')
        ->press('ログアウト')
        ->assertPathIs('/login')
        ->assertSee('ログイン')
        ->assertNoJavascriptErrors();
});
