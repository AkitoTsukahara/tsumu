<?php

declare(strict_types=1);

use Domain\User\UserId;
use Domain\Workout\Exceptions\InvalidWorkoutStateTransitionException;
use Domain\Workout\Workout;
use Domain\Workout\WorkoutId;
use Domain\Workout\WorkoutStatus;

it('開始直後のトレーニングを進行中として扱う', function () {
    $workout = ongoingWorkout();

    expect($workout->status())->toBe(WorkoutStatus::InProgress)
        ->and($workout->completedAt)->toBeNull();
});

it('進行中のトレーニングを終了できる', function () {
    $workout = ongoingWorkout();
    $completedAt = new DateTimeImmutable('2026-10-02 11:00:00');

    $completedWorkout = $workout->complete($completedAt);

    expect($completedWorkout->status())->toBe(WorkoutStatus::Completed)
        ->and($completedWorkout->completedAt)->toBe($completedAt)
        ->and($workout->status())->toBe(WorkoutStatus::InProgress);
});

it('終了済みのトレーニングを再度終了することを拒否する', function () {
    ongoingWorkout()
        ->complete(new DateTimeImmutable('2026-10-02 11:00:00'))
        ->complete(new DateTimeImmutable('2026-10-02 12:00:00'));
})->throws(InvalidWorkoutStateTransitionException::class);

it('開始時刻より前に終了することを拒否する', function () {
    ongoingWorkout()->complete(new DateTimeImmutable('2026-10-02 09:59:59'));
})->throws(InvalidWorkoutStateTransitionException::class);

function ongoingWorkout(): Workout
{
    return new Workout(
        id: WorkoutId::fromString('01990000-0000-7000-8000-000000000002'),
        userId: UserId::fromString('01990000-0000-7000-8000-000000000000'),
        startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
    );
}
