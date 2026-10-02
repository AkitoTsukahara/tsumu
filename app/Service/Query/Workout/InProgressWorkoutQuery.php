<?php

declare(strict_types=1);

namespace App\Service\Query\Workout;

use App\Service\Query\Workout\Dto\InProgressWorkoutDto;
use Domain\User\UserId;

interface InProgressWorkoutQuery
{
    public function forUser(UserId $userId): ?InProgressWorkoutDto;
}
