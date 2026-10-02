<?php

declare(strict_types=1);

namespace App\Service\Query\Workout;

use App\Service\Query\Exercise\Dto\ExerciseListItemCollection;
use Domain\User\UserId;

interface WorkoutExerciseListQuery
{
    public function addedForUser(UserId $userId): ExerciseListItemCollection;

    public function availableForUser(UserId $userId): ExerciseListItemCollection;
}
