<?php

declare(strict_types=1);

namespace Domain\Workout;

use Domain\User\UserId;

interface WorkoutRepository
{
    public function save(Workout $workout): void;

    public function findInProgressByUser(UserId $userId): ?Workout;
}
