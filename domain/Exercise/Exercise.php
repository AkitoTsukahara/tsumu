<?php

declare(strict_types=1);

namespace Domain\Exercise;

use Domain\Equipment\EquipmentId;
use Domain\User\UserId;

final readonly class Exercise
{
    public function __construct(
        public ExerciseId $id,
        public UserId $userId,
        public ExerciseName $name,
        public ?EquipmentId $equipmentId,
        public BodyPart $primaryTarget,
        public ?BodyPart $secondaryTarget,
        public RecordingMethod $recordingMethod,
    ) {}
}
