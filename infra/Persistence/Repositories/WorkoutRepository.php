<?php

declare(strict_types=1);

namespace Infra\Persistence\Repositories;

use Domain\User\UserId;
use Domain\Workout\Workout;
use Domain\Workout\WorkoutRepository as WorkoutRepositoryContract;
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
}
