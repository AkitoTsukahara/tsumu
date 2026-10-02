<?php

declare(strict_types=1);

namespace Domain\Workout;

use DateTimeImmutable;
use Domain\User\UserId;
use Domain\Workout\Exceptions\InvalidWorkoutStateTransitionException;

final readonly class Workout
{
    public function __construct(
        public WorkoutId $id,
        public UserId $userId,
        public DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $completedAt = null,
    ) {
        if ($completedAt !== null && $completedAt < $startedAt) {
            throw new InvalidWorkoutStateTransitionException('終了時刻は開始時刻以降である必要があります。');
        }
    }

    public function status(): WorkoutStatus
    {
        return $this->completedAt === null
            ? WorkoutStatus::InProgress
            : WorkoutStatus::Completed;
    }

    public function complete(DateTimeImmutable $completedAt): self
    {
        if ($this->status() === WorkoutStatus::Completed) {
            throw new InvalidWorkoutStateTransitionException('終了済みのトレーニングは再度終了できません。');
        }

        return new self(
            id: $this->id,
            userId: $this->userId,
            startedAt: $this->startedAt,
            completedAt: $completedAt,
        );
    }
}
