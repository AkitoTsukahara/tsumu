<?php

declare(strict_types=1);

use App\Service\Command\StartWorkout;
use Domain\User\UserId;
use Domain\Workout\WorkoutRepository;
use Infra\Persistence\Eloquent\Models\User;
use Infra\Persistence\Eloquent\Models\Workout as WorkoutModel;

use function Pest\Laravel\travelTo;

it('開始Commandで進行中のトレーニングを保存して再取得できる', function () {
    $user = User::factory()->create();
    $userId = UserId::fromString($user->id);

    $workoutId = travelTo(
        '2026-10-02 10:00:00',
        fn () => app(StartWorkout::class)->handle($userId),
    );

    $workout = app(WorkoutRepository::class)->findInProgressByUser($userId);

    expect($workout)->not->toBeNull()
        ->and($workout->id->value)->toBe($workoutId->value)
        ->and($workout->userId->value)->toBe($userId->value)
        ->and($workout->startedAt->format('Y-m-d H:i:s'))->toBe('2026-10-02 10:00:00')
        ->and($workout->completedAt)->toBeNull();
});

it('進行中のトレーニングがあれば新規作成せず同じIDを返す', function () {
    $user = User::factory()->create();
    $existingWorkout = WorkoutModel::factory()->forUser($user)->create([
        'id' => '01990000-0000-7000-8000-000000000001',
    ]);

    $workoutId = app(StartWorkout::class)->handle(UserId::fromString($user->id));

    expect($workoutId->value)->toBe($existingWorkout->id);
    $this->assertDatabaseCount('workouts', 1);
});

it('終了済みのトレーニングがあれば新しいトレーニングを開始する', function () {
    $user = User::factory()->create();
    $completedWorkout = WorkoutModel::factory()->forUser($user)->completed()->create();

    $workoutId = app(StartWorkout::class)->handle(UserId::fromString($user->id));

    expect($workoutId->value)->not->toBe($completedWorkout->id);
    $this->assertDatabaseCount('workouts', 2);
    $this->assertDatabaseHas('workouts', [
        'id' => $workoutId->value,
        'user_id' => $user->id,
        'completed_at' => null,
    ]);
});

it('別ユーザーの進行中トレーニングを再開しない', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownersWorkout = WorkoutModel::factory()->forUser($owner)->create();

    $workoutId = app(StartWorkout::class)->handle(UserId::fromString($otherUser->id));

    expect($workoutId->value)->not->toBe($ownersWorkout->id);
    $this->assertDatabaseHas('workouts', [
        'id' => $workoutId->value,
        'user_id' => $otherUser->id,
    ]);
});
