<?php

declare(strict_types=1);

namespace App\Service\Command;

use Domain\User\UserId;
use Domain\Workout\Workout;
use Domain\Workout\WorkoutId;
use Domain\Workout\WorkoutRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final readonly class StartWorkout
{
    public function __construct(private WorkoutRepository $workouts) {}

    public function handle(UserId $userId): WorkoutId
    {
        return Cache::lock('workout-start:'.$userId->value, 10)->block(
            5,
            fn (): WorkoutId => $this->startOrResume($userId),
        );
    }

    private function startOrResume(UserId $userId): WorkoutId
    {
        $inProgressWorkout = $this->workouts->findInProgressByUser($userId);

        if ($inProgressWorkout !== null) {
            return $inProgressWorkout->id;
        }

        $workout = new Workout(
            id: WorkoutId::fromString((string) Str::uuid7()),
            userId: $userId,
            startedAt: now()->toDateTimeImmutable(),
        );

        $this->workouts->save($workout);

        return $workout->id;
    }
}
