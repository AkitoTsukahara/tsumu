<?php

declare(strict_types=1);

namespace Domain\Workout;

enum WorkoutStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
