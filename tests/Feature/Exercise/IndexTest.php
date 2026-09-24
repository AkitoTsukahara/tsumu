<?php

use Domain\Exercise\BodyPart;
use Domain\Exercise\RecordingMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\User;

uses(RefreshDatabase::class);

it('未認証ユーザーをログイン画面へ移動させる', function () {
    $this->get('/settings/exercises')
        ->assertRedirect(route('login'));
});

it('種目がない場合に空状態を表示する', function () {
    $this->withoutVite();

    $this->actingAs(User::factory()->create())
        ->get('/settings/exercises')
        ->assertOk()
        ->assertSee('種目はまだありません');
});

it('自分の種目だけを安全に表示する', function () {
    $this->withoutVite();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $equipment = Equipment::factory()->forUser($user)->create(['name' => 'レッグプレス機材']);
    Exercise::factory()->forEquipment($equipment)->create([
        'name' => '<script>alert("xss")</script>',
        'primary_target' => BodyPart::Quadriceps,
        'secondary_target' => BodyPart::Glutes,
        'recording_method' => RecordingMethod::WeightAndRepetitions,
    ]);
    Exercise::factory()->forUser($otherUser)->create(['name' => '他ユーザーの種目']);

    $this->actingAs($user)
        ->get('/settings/exercises')
        ->assertOk()
        ->assertSeeText('<script>alert("xss")</script>')
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertSee('レッグプレス機材')
        ->assertSee('重量＋回数')
        ->assertSee('大腿四頭筋')
        ->assertSee('臀部')
        ->assertDontSee('他ユーザーの種目');
});
