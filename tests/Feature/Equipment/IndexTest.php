<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\User;

uses(RefreshDatabase::class);

it('未認証ユーザーをログイン画面へ移動させる', function () {
    $this->get('/settings/equipment')
        ->assertRedirect(route('login'));
});

it('機材がない場合に空状態を表示する', function () {
    $this->withoutVite();

    $this->actingAs(User::factory()->create())
        ->get('/settings/equipment')
        ->assertOk()
        ->assertSee('機材はまだありません');
});

it('自分の機材だけを安全に表示する', function () {
    $this->withoutVite();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Equipment::factory()->forUser($user)->create([
        'name' => '<script>alert("xss")</script>',
        'weight_increment' => '9.00',
    ]);
    Equipment::factory()->forUser($otherUser)->create(['name' => '他ユーザーの機材']);

    $this->actingAs($user)
        ->get('/settings/equipment')
        ->assertOk()
        ->assertSeeText('<script>alert("xss")</script>')
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertSee('9.00 kg')
        ->assertDontSee('他ユーザーの機材');
});
