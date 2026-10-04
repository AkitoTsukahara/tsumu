<?php

declare(strict_types=1);

namespace Infra\Persistence\Mappers;

use DateTimeInterface;
use Domain\Exercise\ExerciseId;
use Domain\Workout\WorkoutId;
use Domain\Workout\WorkoutSet;
use Domain\Workout\WorkoutSetId;
use Domain\Workout\WorkoutSetPosition;
use Domain\Workout\WorkoutSetRepetitions;
use Domain\Workout\WorkoutSetWeight;
use Infra\Persistence\Eloquent\Models\WorkoutSet as WorkoutSetModel;

final class WorkoutSetMapper
{
    /**
     * @return array{
     *     id: string,
     *     workout_id: string,
     *     exercise_id: string,
     *     position: int,
     *     weight: string,
     *     repetitions: int,
     *     completed_at: DateTimeInterface
     * }
     */
    public function toPersistence(WorkoutSet $workoutSet): array
    {
        return [
            'id' => $workoutSet->id->value,
            'workout_id' => $workoutSet->workoutId->value,
            'exercise_id' => $workoutSet->exerciseId->value,
            'position' => $workoutSet->position->value,
            'weight' => $workoutSet->weight->toDecimal(),
            'repetitions' => $workoutSet->repetitions->value,
            'completed_at' => $workoutSet->completedAt,
        ];
    }

    public function toDomain(WorkoutSetModel $model): WorkoutSet
    {
        return new WorkoutSet(
            id: WorkoutSetId::fromString($model->id),
            workoutId: WorkoutId::fromString($model->workout_id),
            exerciseId: ExerciseId::fromString($model->exercise_id),
            position: WorkoutSetPosition::fromInt($model->position),
            weight: WorkoutSetWeight::fromDecimal($model->weight),
            repetitions: WorkoutSetRepetitions::fromInt($model->repetitions),
            completedAt: $model->completed_at->toDateTimeImmutable(),
        );
    }
}
