<?php

declare(strict_types=1);

namespace Infra\Persistence\Mappers;

use DateTimeInterface;
use Domain\User\UserId;
use Domain\Workout\Workout;
use Domain\Workout\WorkoutId;
use Infra\Persistence\Eloquent\Models\Workout as WorkoutModel;

final class WorkoutMapper
{
    /**
     * @return array{
     *     id: string,
     *     user_id: string,
     *     started_at: DateTimeInterface,
     *     completed_at: DateTimeInterface|null
     * }
     */
    public function toPersistence(Workout $workout): array
    {
        return [
            'id' => $workout->id->value,
            'user_id' => $workout->userId->value,
            'started_at' => $workout->startedAt,
            'completed_at' => $workout->completedAt,
        ];
    }

    public function toDomain(WorkoutModel $model): Workout
    {
        return new Workout(
            id: WorkoutId::fromString($model->id),
            userId: UserId::fromString($model->user_id),
            startedAt: $model->started_at->toDateTimeImmutable(),
            completedAt: $model->completed_at?->toDateTimeImmutable(),
        );
    }
}
