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
        ->click('機材を管理')
        ->assertPathIs('/settings/equipment')
        ->assertSee('機材はまだありません')
        ->press('ログアウト')
        ->assertPathIs('/login')
        ->assertSee('ログイン')
        ->assertNoJavascriptErrors();
});

test('認証済みユーザーが機材を登録できる', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    visit('/settings/equipment')
        ->resize(360, 800)
        ->assertSee('機材はまだありません')
        ->type('name', 'レッグプレス')
        ->type('weight_increment', '9')
        ->press('登録する')
        ->assertSee('機材を登録しました。')
        ->assertSee('レッグプレス')
        ->assertSee('9.00 kg')
        ->click('レッグプレスを編集')
        ->assertSee('機材を編集')
        ->type('name', 'レッグプレス45')
        ->type('weight_increment', '4.5')
        ->press('更新する')
        ->assertSee('機材を更新しました。')
        ->assertSee('レッグプレス45')
        ->assertSee('4.50 kg')
        ->assertNoJavascriptErrors();
});
