<?php

namespace App\Service\Command;

use Domain\Equipment\EquipmentId;
use Domain\Equipment\EquipmentRepository;
use Domain\Exercise\BodyPart;
use Domain\Exercise\Exercise;
use Domain\Exercise\ExerciseId;
use Domain\Exercise\ExerciseName;
use Domain\Exercise\ExerciseRepository;
use Domain\User\UserId;

final readonly class UpdateExercise
{
    public function __construct(
        private ExerciseRepository $exercises,
        private EquipmentRepository $equipment,
    ) {}

    public function handle(
        ExerciseId $exerciseId,
        UserId $userId,
        string $name,
        ?EquipmentId $equipmentId,
        BodyPart $primaryTarget,
        ?BodyPart $secondaryTarget,
    ): bool {
        $exercise = $this->exercises->findOwnedBy($exerciseId, $userId);

        if ($exercise === null) {
            return false;
        }

        if ($equipmentId !== null && $this->equipment->findOwnedBy($equipmentId, $userId) === null) {
            return false;
        }

        return $this->exercises->update(new Exercise(
            id: $exercise->id,
            userId: $exercise->userId,
            name: ExerciseName::fromString($name),
            equipmentId: $equipmentId,
            primaryTarget: $primaryTarget,
            secondaryTarget: $secondaryTarget,
            recordingMethod: $exercise->recordingMethod,
        ));
    }
}
