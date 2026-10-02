<?php

use App\Livewire\Today;
use App\Livewire\Workout\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\Persistence\Eloquent\Models\Exercise;
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

it('所有する種目だけを選んで進行中トレーニングへ追加する', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $workout = Workout::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->forUser($user)->create(['name' => 'レッグプレス']);
    Exercise::factory()->forUser($otherUser)->create(['name' => '他ユーザーの種目']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('レッグプレス')
        ->assertDontSee('他ユーザーの種目')
        ->set('selectedExerciseId', $exercise->id)
        ->call('addExercise')
        ->assertHasNoErrors()
        ->assertSet('selectedExerciseId', '')
        ->assertSee('種目を追加しました。')
        ->assertSee('1種目目')
        ->assertSee('レッグプレス');

    $this->assertDatabaseHas('workout_exercises', [
        'workout_id' => $workout->id,
        'exercise_id' => $exercise->id,
        'position' => 1,
    ]);
});

it('種目名を安全に表示する', function () {
    $user = User::factory()->create();
    Workout::factory()->forUser($user)->create();
    Exercise::factory()->forUser($user)->create([
        'name' => '<script>alert("xss")</script>',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSeeText('<script>alert("xss")</script>')
        ->assertDontSee('<script>alert("xss")</script>', false);
});

it('種目を選択しなければ追加しない', function () {
    $user = User::factory()->create();
    Workout::factory()->forUser($user)->create();
    Exercise::factory()->forUser($user)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('addExercise')
        ->assertHasErrors(['selectedExerciseId' => 'required'])
        ->assertSee('追加する種目を選択してください。');

    $this->assertDatabaseCount('workout_exercises', 0);
});

it('別ユーザーの種目を指定しても追加しない', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Workout::factory()->forUser($user)->create();
    $otherExercise = Exercise::factory()->forUser($otherUser)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('selectedExerciseId', $otherExercise->id)
        ->call('addExercise')
        ->assertNotFound();

    $this->assertDatabaseCount('workout_exercises', 0);
});
