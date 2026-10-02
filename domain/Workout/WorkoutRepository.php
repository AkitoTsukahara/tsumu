<?php

declare(strict_types=1);

namespace Domain\Workout;

use Domain\Exercise\ExerciseId;
use Domain\User\UserId;

interface WorkoutRepository
{
    public function save(Workout $workout): void;

    public function findInProgressByUser(UserId $userId): ?Workout;

    public function addExercise(WorkoutId $workoutId, ExerciseId $exerciseId): void;
}
