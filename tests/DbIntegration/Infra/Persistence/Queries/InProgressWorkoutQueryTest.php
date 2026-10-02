<?php

declare(strict_types=1);

use App\Service\Query\Workout\InProgressWorkoutQuery;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\User;
use Infra\Persistence\Eloquent\Models\Workout;

it('自分の進行中トレーニングだけを返す', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Workout::factory()->forUser($user)->completed()->create();
    Workout::factory()->forUser($otherUser)->create();
    Workout::factory()->forUser($user)->create([
        'id' => '01990000-0000-7000-8000-000000000001',
        'started_at' => '2026-10-02 10:00:00',
    ]);

    $workout = app(InProgressWorkoutQuery::class)->forUser(UserId::fromString($user->id));

    expect($workout)->not->toBeNull()
        ->and($workout->id)->toBe('01990000-0000-7000-8000-000000000001')
        ->and($workout->startedAt->format('Y-m-d H:i:s'))->toBe('2026-10-02 10:00:00');
});

it('自分の進行中トレーニングがなければnullを返す', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Workout::factory()->forUser($user)->completed()->create();
    Workout::factory()->forUser($otherUser)->create();

    $workout = app(InProgressWorkoutQuery::class)->forUser(UserId::fromString($user->id));

    expect($workout)->toBeNull();
});
