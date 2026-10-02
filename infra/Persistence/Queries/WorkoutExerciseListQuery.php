<?php

declare(strict_types=1);

namespace Infra\Persistence\Queries;

use App\Service\Query\Exercise\Dto\ExerciseListItemCollection;
use App\Service\Query\Exercise\Dto\ExerciseListItemDto;
use App\Service\Query\Workout\WorkoutExerciseListQuery as WorkoutExerciseListQueryContract;
use Domain\User\UserId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\Workout;

final class WorkoutExerciseListQuery implements WorkoutExerciseListQueryContract
{
    public function addedForUser(UserId $userId): ExerciseListItemCollection
    {
        $workoutId = $this->inProgressWorkoutId($userId);

        if ($workoutId === null) {
            return new ExerciseListItemCollection([]);
        }

        return $this->items(
            $this->exerciseQuery($userId)
                ->join('workout_exercises', 'workout_exercises.exercise_id', '=', 'exercises.id')
                ->where('workout_exercises.workout_id', $workoutId)
                ->orderBy('workout_exercises.position'),
        );
    }

    public function availableForUser(UserId $userId): ExerciseListItemCollection
    {
        $workoutId = $this->inProgressWorkoutId($userId);

        if ($workoutId === null) {
            return new ExerciseListItemCollection([]);
        }

        return $this->items(
            $this->exerciseQuery($userId)
                ->whereNotExists(function ($query) use ($workoutId): void {
                    $query->selectRaw('1')
                        ->from('workout_exercises')
                        ->whereColumn('workout_exercises.exercise_id', 'exercises.id')
                        ->where('workout_exercises.workout_id', $workoutId);
                })
                ->orderBy('exercises.name')
                ->orderBy('exercises.id'),
        );
    }

    private function inProgressWorkoutId(UserId $userId): ?string
    {
        return Workout::query()
            ->where('user_id', $userId->value)
            ->whereNull('completed_at')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->value('id');
    }

    /** @return Builder<Exercise> */
    private function exerciseQuery(UserId $userId): Builder
    {
        return Exercise::query()
            ->leftJoin('equipment', function (JoinClause $join): void {
                $join->on('equipment.id', '=', 'exercises.equipment_id')
                    ->on('equipment.user_id', '=', 'exercises.user_id');
            })
            ->where('exercises.user_id', $userId->value);
    }

    /** @param Builder<Exercise> $query */
    private function items(Builder $query): ExerciseListItemCollection
    {
        $items = $query
            ->get([
                'exercises.id',
                'exercises.name',
                'exercises.primary_target',
                'exercises.secondary_target',
                'exercises.recording_method',
                'equipment.id as equipment_id',
                'equipment.name as equipment_name',
            ])
            ->map(fn (Exercise $exercise): ExerciseListItemDto => new ExerciseListItemDto(
                id: $exercise->id,
                name: $exercise->name,
                equipmentId: $exercise->equipment_id,
                equipmentName: $exercise->equipment_name,
                primaryTarget: $exercise->primary_target->value,
                secondaryTarget: $exercise->secondary_target?->value,
                recordingMethod: $exercise->recording_method->value,
            ))
            ->all();

        return new ExerciseListItemCollection($items);
    }
}
