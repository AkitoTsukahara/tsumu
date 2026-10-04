<?php

declare(strict_types=1);

namespace App\Service\Command;

use Domain\Exercise\ExerciseId;
use Domain\Exercise\ExerciseRepository;
use Domain\Exercise\RecordingMethod;
use Domain\User\UserId;
use Domain\Workout\WorkoutId;
use Domain\Workout\WorkoutRepository;
use Domain\Workout\WorkoutSet;
use Domain\Workout\WorkoutSetId;
use Domain\Workout\WorkoutSetRepetitions;
use Domain\Workout\WorkoutSetRepository;
use Domain\Workout\WorkoutSetWeight;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final readonly class RecordWorkoutSet
{
    public function __construct(
        private WorkoutRepository $workouts,
        private WorkoutSetRepository $workoutSets,
        private ExerciseRepository $exercises,
    ) {}

    public function handle(
        UserId $userId,
        ExerciseId $exerciseId,
        string $weight,
        int $repetitions,
    ): ?WorkoutSetId {
        $workout = $this->workouts->findInProgressByUser($userId);
        $exercise = $this->exercises->findOwnedBy($exerciseId, $userId);

        if ($workout === null || $exercise?->recordingMethod !== RecordingMethod::WeightAndRepetitions) {
            return null;
        }

        return Cache::lock(
            'workout-set-record:'.$workout->id->value.':'.$exerciseId->value,
            10,
        )->block(
            5,
            fn (): ?WorkoutSetId => $this->record(
                $userId,
                $workout->id,
                $exerciseId,
                $weight,
                $repetitions,
            ),
        );
    }

    private function record(
        UserId $userId,
        WorkoutId $workoutId,
        ExerciseId $exerciseId,
        string $weight,
        int $repetitions,
    ): ?WorkoutSetId {
        $position = $this->workoutSets->nextPositionFor($workoutId, $exerciseId);

        if ($position === null) {
            return null;
        }

        $workoutSet = new WorkoutSet(
            id: WorkoutSetId::fromString((string) Str::uuid7()),
            workoutId: $workoutId,
            exerciseId: $exerciseId,
            position: $position,
            weight: WorkoutSetWeight::fromDecimal($weight),
            repetitions: WorkoutSetRepetitions::fromInt($repetitions),
            completedAt: now()->toDateTimeImmutable(),
        );

        return $this->workoutSets->saveToInProgressWorkout($workoutSet, $userId)
            ? $workoutSet->id
            : null;
    }
}
