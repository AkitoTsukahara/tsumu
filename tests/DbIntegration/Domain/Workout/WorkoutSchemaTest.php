<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Infra\Persistence\Eloquent\Models\User;
use Infra\Persistence\Eloquent\Models\Workout;

it('進行中のトレーニングをユーザーに紐づけて保存できる', function () {
    $user = User::factory()->create();

    $workout = Workout::factory()->forUser($user)->create();

    expect(Str::isUuid($workout->id, version: 7))->toBeTrue()
        ->and($workout->user_id)->toBe($user->id)
        ->and($workout->started_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($workout->completed_at)->toBeNull();
});

it('終了時刻を持つトレーニングを保存できる', function () {
    $workout = Workout::factory()->completed()->create();

    expect($workout->completed_at)->toBeInstanceOf(CarbonImmutable::class);
});

it('ユーザーを削除すると所有するトレーニングも削除する', function () {
    $user = User::factory()->create();
    $workout = Workout::factory()->forUser($user)->create();

    $user->delete();

    $this->assertDatabaseMissing('workouts', ['id' => $workout->id]);
});
