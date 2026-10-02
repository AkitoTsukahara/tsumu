<?php

declare(strict_types=1);

namespace Infra\Persistence\Queries;

use App\Service\Query\Exercise\Dto\ExerciseListItemCollection;
use App\Service\Query\Exercise\Dto\ExerciseListItemDto;
use App\Service\Query\Exercise\ExerciseListQuery as ExerciseListQueryContract;
use Domain\User\UserId;
use Illuminate\Database\Query\JoinClause;
use Infra\Persistence\Eloquent\Models\Exercise;

final class ExerciseListQuery implements ExerciseListQueryContract
{
    public function forUser(UserId $userId): ExerciseListItemCollection
    {
        $items = Exercise::query()
            ->leftJoin('equipment', function (JoinClause $join): void {
                $join->on('equipment.id', '=', 'exercises.equipment_id')
                    ->on('equipment.user_id', '=', 'exercises.user_id');
            })
            ->where('exercises.user_id', $userId->value)
            ->orderBy('exercises.name')
            ->orderBy('exercises.id')
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
