<?php

namespace App\Service\Command;

use Domain\Equipment\EquipmentId;
use Domain\Equipment\EquipmentRepository;
use Domain\Exercise\BodyPart;
use Domain\Exercise\Exercise;
use Domain\Exercise\ExerciseId;
use Domain\Exercise\ExerciseName;
use Domain\Exercise\ExerciseRepository;
use Domain\Exercise\RecordingMethod;
use Domain\User\UserId;
use Illuminate\Support\Str;

final readonly class CreateExercise
{
    public function __construct(
        private ExerciseRepository $exercises,
        private EquipmentRepository $equipment,
    ) {}

    public function handle(
        UserId $userId,
        string $name,
        ?EquipmentId $equipmentId,
        BodyPart $primaryTarget,
        ?BodyPart $secondaryTarget,
        RecordingMethod $recordingMethod,
    ): ?ExerciseId {
        if ($equipmentId !== null && $this->equipment->findOwnedBy($equipmentId, $userId) === null) {
            return null;
        }

        $exercise = new Exercise(
            id: ExerciseId::fromString((string) Str::uuid7()),
            userId: $userId,
            name: ExerciseName::fromString($name),
            equipmentId: $equipmentId,
            primaryTarget: $primaryTarget,
            secondaryTarget: $secondaryTarget,
            recordingMethod: $recordingMethod,
        );

        $this->exercises->save($exercise);

        return $exercise->id;
    }
}
