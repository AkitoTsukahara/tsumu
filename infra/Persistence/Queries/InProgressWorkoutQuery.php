<?php

declare(strict_types=1);

namespace Infra\Persistence\Queries;

use App\Service\Query\Workout\Dto\InProgressWorkoutDto;
use App\Service\Query\Workout\InProgressWorkoutQuery as InProgressWorkoutQueryContract;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Workout;

final class InProgressWorkoutQuery implements InProgressWorkoutQueryContract
{
    public function forUser(UserId $userId): ?InProgressWorkoutDto
    {
        $workout = Workout::query()
            ->where('user_id', $userId->value)
            ->whereNull('completed_at')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->first(['id', 'started_at']);

        if ($workout === null) {
            return null;
        }

        return new InProgressWorkoutDto(
            id: $workout->id,
            startedAt: $workout->started_at->toDateTimeImmutable(),
        );
    }
}
