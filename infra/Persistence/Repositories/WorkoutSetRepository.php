<?php

declare(strict_types=1);

namespace Infra\Persistence\Repositories;

use Domain\Exercise\ExerciseId;
use Domain\User\UserId;
use Domain\Workout\WorkoutId;
use Domain\Workout\WorkoutSet;
use Domain\Workout\WorkoutSetId;
use Domain\Workout\WorkoutSetPosition;
use Domain\Workout\WorkoutSetRepository as WorkoutSetRepositoryContract;
use Illuminate\Support\Facades\DB;
use Infra\Persistence\Eloquent\Models\Workout as WorkoutModel;
use Infra\Persistence\Eloquent\Models\WorkoutSet as WorkoutSetModel;
use Infra\Persistence\Mappers\WorkoutSetMapper;

final readonly class WorkoutSetRepository implements WorkoutSetRepositoryContract
{
    public function __construct(private WorkoutSetMapper $mapper) {}

    public function nextPositionFor(WorkoutId $workoutId, ExerciseId $exerciseId): ?WorkoutSetPosition
    {
        $exerciseIsAdded = DB::table('workout_exercises')
            ->where('workout_id', $workoutId->value)
            ->where('exercise_id', $exerciseId->value)
            ->exists();

        if (! $exerciseIsAdded) {
            return null;
        }

        $lastPosition = WorkoutSetModel::query()
            ->where('workout_id', $workoutId->value)
            ->where('exercise_id', $exerciseId->value)
            ->max('position');

        return WorkoutSetPosition::fromInt(((int) $lastPosition) + 1);
    }

    public function saveToInProgressWorkout(WorkoutSet $workoutSet, UserId $userId): bool
    {
        return DB::transaction(function () use ($workoutSet, $userId): bool {
            $workout = WorkoutModel::query()
                ->whereKey($workoutSet->workoutId->value)
                ->where('user_id', $userId->value)
                ->whereNull('completed_at')
                ->lockForUpdate()
                ->first(['id']);

            if ($workout === null || $this->nextPositionFor(
                $workoutSet->workoutId,
                $workoutSet->exerciseId,
            )?->value !== $workoutSet->position->value) {
                return false;
            }

            WorkoutSetModel::query()->create($this->mapper->toPersistence($workoutSet));

            return true;
        });
    }

    public function findOwnedBy(WorkoutSetId $workoutSetId, UserId $userId): ?WorkoutSet
    {
        $model = WorkoutSetModel::query()
            ->whereKey($workoutSetId->value)
            ->whereIn(
                'workout_id',
                WorkoutModel::query()->select('id')->where('user_id', $userId->value),
            )
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }
}
