<?php

declare(strict_types=1);

namespace Infra\Persistence\Repositories;

use Domain\Exercise\Exercise;
use Domain\Exercise\ExerciseId;
use Domain\Exercise\ExerciseRepository as ExerciseRepositoryContract;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Exercise as ExerciseModel;
use Infra\Persistence\Mappers\ExerciseMapper;

final class ExerciseRepository implements ExerciseRepositoryContract
{
    public function __construct(private readonly ExerciseMapper $mapper) {}

    public function save(Exercise $exercise): void
    {
        ExerciseModel::query()->create($this->mapper->toPersistence($exercise));
    }

    public function findOwnedBy(ExerciseId $exerciseId, UserId $userId): ?Exercise
    {
        $model = ExerciseModel::query()
            ->whereKey($exerciseId->value)
            ->where('user_id', $userId->value)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }
}
