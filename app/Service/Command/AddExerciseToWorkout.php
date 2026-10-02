<?php

declare(strict_types=1);

namespace App\Service\Command;

use Domain\Exercise\ExerciseId;
use Domain\Exercise\ExerciseRepository;
use Domain\User\UserId;
use Domain\Workout\WorkoutRepository;
use Illuminate\Support\Facades\Cache;

final readonly class AddExerciseToWorkout
{
    public function __construct(
        private WorkoutRepository $workouts,
        private ExerciseRepository $exercises,
    ) {}

    public function handle(UserId $userId, ExerciseId $exerciseId): bool
    {
        $workout = $this->workouts->findInProgressByUser($userId);

        if ($workout === null || $this->exercises->findOwnedBy($exerciseId, $userId) === null) {
            return false;
        }

        Cache::lock('workout-exercise-add:'.$workout->id->value, 10)->block(
            5,
            fn () => $this->workouts->addExercise($workout->id, $exerciseId),
        );

        return true;
    }
}
