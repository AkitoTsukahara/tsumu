<?php

declare(strict_types=1);

namespace Infra\Persistence\Mappers;

use Domain\Equipment\EquipmentId;
use Domain\Exercise\BodyPart;
use Domain\Exercise\Exercise;
use Domain\Exercise\ExerciseId;
use Domain\Exercise\ExerciseName;
use Domain\Exercise\RecordingMethod;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Exercise as ExerciseModel;

final class ExerciseMapper
{
    /**
     * @return array{
     *     id: string,
     *     user_id: string,
     *     equipment_id: string|null,
     *     name: string,
     *     primary_target: BodyPart,
     *     secondary_target: BodyPart|null,
     *     recording_method: RecordingMethod
     * }
     */
    public function toPersistence(Exercise $exercise): array
    {
        return [
            'id' => $exercise->id->value,
            'user_id' => $exercise->userId->value,
            'equipment_id' => $exercise->equipmentId?->value,
            'name' => $exercise->name->value,
            'primary_target' => $exercise->primaryTarget,
            'secondary_target' => $exercise->secondaryTarget,
            'recording_method' => $exercise->recordingMethod,
        ];
    }

    public function toDomain(ExerciseModel $model): Exercise
    {
        return new Exercise(
            id: ExerciseId::fromString($model->id),
            userId: UserId::fromString($model->user_id),
            name: ExerciseName::fromString($model->name),
            equipmentId: $model->equipment_id === null
                ? null
                : EquipmentId::fromString($model->equipment_id),
            primaryTarget: $model->primary_target,
            secondaryTarget: $model->secondary_target,
            recordingMethod: $model->recording_method,
        );
    }
}
