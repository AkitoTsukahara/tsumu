<?php

declare(strict_types=1);

namespace Domain\Workout;

use Domain\Exercise\ExerciseId;
use Domain\User\UserId;

interface WorkoutSetRepository
{
    public function nextPositionFor(WorkoutId $workoutId, ExerciseId $exerciseId): ?WorkoutSetPosition;

    public function saveToInProgressWorkout(WorkoutSet $workoutSet, UserId $userId): bool;

    public function findOwnedBy(WorkoutSetId $workoutSetId, UserId $userId): ?WorkoutSet;
}
