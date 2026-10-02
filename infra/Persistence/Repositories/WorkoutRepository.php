<?php

declare(strict_types=1);

namespace Infra\Persistence\Repositories;

use Domain\Exercise\ExerciseId;
use Domain\User\UserId;
use Domain\Workout\Workout;
use Domain\Workout\WorkoutId;
use Domain\Workout\WorkoutRepository as WorkoutRepositoryContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Infra\Persistence\Eloquent\Models\Workout as WorkoutModel;
use Infra\Persistence\Mappers\WorkoutMapper;

final readonly class WorkoutRepository implements WorkoutRepositoryContract
{
    public function __construct(private WorkoutMapper $mapper) {}

    public function save(Workout $workout): void
    {
        WorkoutModel::query()->create($this->mapper->toPersistence($workout));
    }

    public function findInProgressByUser(UserId $userId): ?Workout
    {
        $model = WorkoutModel::query()
            ->where('user_id', $userId->value)
            ->whereNull('completed_at')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function addExercise(WorkoutId $workoutId, ExerciseId $exerciseId): void
    {
        DB::transaction(function () use ($workoutId, $exerciseId): void {
            $alreadyAdded = DB::table('workout_exercises')
                ->where('workout_id', $workoutId->value)
                ->where('exercise_id', $exerciseId->value)
                ->exists();

            if ($alreadyAdded) {
                return;
            }

            $lastPosition = DB::table('workout_exercises')
                ->where('workout_id', $workoutId->value)
                ->max('position');

            DB::table('workout_exercises')->insert([
                'id' => (string) Str::uuid7(),
                'workout_id' => $workoutId->value,
                'exercise_id' => $exerciseId->value,
                'position' => ((int) $lastPosition) + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
