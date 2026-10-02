<?php

declare(strict_types=1);

namespace Domain\Workout;

use DateTimeImmutable;
use Domain\Exercise\ExerciseId;

final readonly class WorkoutSet
{
    public function __construct(
        public WorkoutSetId $id,
        public WorkoutId $workoutId,
        public ExerciseId $exerciseId,
        public WorkoutSetPosition $position,
        public WorkoutSetWeight $weight,
        public WorkoutSetRepetitions $repetitions,
        public DateTimeImmutable $completedAt,
    ) {}
}
