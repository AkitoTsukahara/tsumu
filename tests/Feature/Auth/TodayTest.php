<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\Persistence\Eloquent\Models\User;

uses(RefreshDatabase::class);

it('未認証ユーザーをログイン画面へ移動させる', function () {
    $this->get('/')
        ->assertRedirect(route('login'));
});

it('認証済みユーザーにToday画面を表示する', function () {
    $this->withoutVite();

    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertOk()
        ->assertSee('今日も積みましょう')
        ->assertSee('ログアウト');
});

it('ログアウトしてセッションを破棄する', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->withSession(['temporary' => 'value']);
    $previousSessionId = session()->getId();
    $previousToken = session()->token();

    $this->post('/logout')
        ->assertRedirect(route('login'))
        ->assertSessionMissing('temporary');

    $this->assertGuest();
    expect(session()->getId())->not->toBe($previousSessionId)
        ->and(session()->token())->not->toBe($previousToken);

    $this->get('/')
        ->assertRedirect(route('login'));
});

it('未認証ユーザーはログアウト処理を実行できない', function () {
    $this->post('/logout')
        ->assertRedirect(route('login'));
});
