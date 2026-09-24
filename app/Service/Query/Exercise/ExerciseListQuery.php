<?php

declare(strict_types=1);

namespace App\Service\Query\Exercise;

use App\Service\Query\Exercise\Dto\ExerciseListItemCollection;
use Domain\User\UserId;

interface ExerciseListQuery
{
    public function forUser(UserId $userId): ExerciseListItemCollection;
}
