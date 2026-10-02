<?php

use App\Livewire\Today;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\Persistence\Eloquent\Models\User;
use Infra\Persistence\Eloquent\Models\Workout;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('未認証ユーザーをログイン画面へ移動させる', function () {
    $this->get('/workout')
        ->assertRedirect(route('login'));
});

it('Todayからフリートレーニングを開始する', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Today::class)
        ->assertSee('フリートレーニングを始める')
        ->call('start')
        ->assertRedirectToRoute('workout');

    $this->assertDatabaseHas('workouts', [
        'user_id' => $user->id,
        'completed_at' => null,
    ]);
});

it('進行中のトレーニングを重複させずに再開する', function () {
    $user = User::factory()->create();
    Workout::factory()->forUser($user)->create();
    $this->actingAs($user);

    Livewire::test(Today::class)
        ->assertSee('トレーニングを再開する')
        ->call('start')
        ->assertRedirectToRoute('workout');

    expect(Workout::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('自分の進行中トレーニングを表示する', function () {
    $user = User::factory()->create();
    Workout::factory()->forUser($user)->create([
        'started_at' => '2026-10-02 09:30:00',
    ]);

    $this->withoutVite();

    $this->actingAs($user)
        ->get('/workout')
        ->assertOk()
        ->assertSee('トレーニング中')
        ->assertSee('2026年10月2日 09:30 開始')
        ->assertSee('種目はまだありません');
});

it('自分の進行中トレーニングがなければTodayへ戻す', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Workout::factory()->forUser($otherUser)->create();

    $this->actingAs($user)
        ->get('/workout')
        ->assertRedirect(route('today'));
});
