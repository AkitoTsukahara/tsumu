<?php

declare(strict_types=1);

namespace Domain\Exercise;

use Domain\User\UserId;

interface ExerciseRepository
{
    public function save(Exercise $exercise): void;

    public function update(Exercise $exercise): bool;

    public function findOwnedBy(ExerciseId $exerciseId, UserId $userId): ?Exercise;
}
