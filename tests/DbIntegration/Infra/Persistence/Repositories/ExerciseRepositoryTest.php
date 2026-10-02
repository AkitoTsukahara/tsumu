<?php

declare(strict_types=1);

use App\Service\Command\CreateExercise;
use App\Service\Command\UpdateExercise;
use Domain\Equipment\EquipmentId;
use Domain\Exercise\BodyPart;
use Domain\Exercise\ExerciseRepository;
use Domain\Exercise\RecordingMethod;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\User;

it('登録Commandで所有する機材に紐づく種目を保存して再取得できる', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->forUser($user)->create();
    $userId = UserId::fromString($user->id);

    $exerciseId = app(CreateExercise::class)->handle(
        userId: $userId,
        name: ' レッグプレス ',
        equipmentId: EquipmentId::fromString($equipment->id),
        primaryTarget: BodyPart::Quadriceps,
        secondaryTarget: BodyPart::Glutes,
        recordingMethod: RecordingMethod::WeightAndRepetitions,
    );

    expect($exerciseId)->not->toBeNull();

    $exercise = app(ExerciseRepository::class)->findOwnedBy($exerciseId, $userId);

    expect($exercise)->not->toBeNull()
        ->and($exercise->id->value)->toBe($exerciseId->value)
        ->and($exercise->userId->value)->toBe($user->id)
        ->and($exercise->name->value)->toBe('レッグプレス')
        ->and($exercise->equipmentId?->value)->toBe($equipment->id)
        ->and($exercise->primaryTarget)->toBe(BodyPart::Quadriceps)
        ->and($exercise->secondaryTarget)->toBe(BodyPart::Glutes)
        ->and($exercise->recordingMethod)->toBe(RecordingMethod::WeightAndRepetitions);
});

it('機材を指定せずに種目を保存できる', function () {
    $user = User::factory()->create();
    $userId = UserId::fromString($user->id);

    $exerciseId = app(CreateExercise::class)->handle(
        userId: $userId,
        name: 'プランク',
        equipmentId: null,
        primaryTarget: BodyPart::Core,
        secondaryTarget: null,
        recordingMethod: RecordingMethod::Duration,
    );

    expect($exerciseId)->not->toBeNull();

    $exercise = app(ExerciseRepository::class)->findOwnedBy($exerciseId, $userId);

    expect($exercise)->not->toBeNull()
        ->and($exercise->equipmentId)->toBeNull()
        ->and($exercise->secondaryTarget)->toBeNull();
});

it('別ユーザーが所有する機材には種目を紐づけない', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherUsersEquipment = Equipment::factory()->forUser($otherUser)->create();

    $exerciseId = app(CreateExercise::class)->handle(
        userId: UserId::fromString($user->id),
        name: 'レッグプレス',
        equipmentId: EquipmentId::fromString($otherUsersEquipment->id),
        primaryTarget: BodyPart::Quadriceps,
        secondaryTarget: null,
        recordingMethod: RecordingMethod::WeightAndRepetitions,
    );

    expect($exerciseId)->toBeNull();
    $this->assertDatabaseCount('exercises', 0);
});

it('別ユーザーが所有する種目は再取得しない', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownerId = UserId::fromString($owner->id);
    $exerciseId = app(CreateExercise::class)->handle(
        userId: $ownerId,
        name: 'プランク',
        equipmentId: null,
        primaryTarget: BodyPart::Core,
        secondaryTarget: null,
        recordingMethod: RecordingMethod::Duration,
    );

    $exercise = app(ExerciseRepository::class)->findOwnedBy(
        $exerciseId,
        UserId::fromString($otherUser->id),
    );

    expect($exercise)->toBeNull();
});

it('所有する種目を更新して記録方式を維持する', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->forUser($user)->create();
    $userId = UserId::fromString($user->id);
    $exerciseId = app(CreateExercise::class)->handle(
        userId: $userId,
        name: 'ランニング',
        equipmentId: null,
        primaryTarget: BodyPart::FullBody,
        secondaryTarget: null,
        recordingMethod: RecordingMethod::DurationAndDistance,
    );

    $updated = app(UpdateExercise::class)->handle(
        exerciseId: $exerciseId,
        userId: $userId,
        name: ' トレッドミルランニング ',
        equipmentId: EquipmentId::fromString($equipment->id),
        primaryTarget: BodyPart::Calves,
        secondaryTarget: BodyPart::Core,
    );

    expect($updated)->toBeTrue();

    $exercise = app(ExerciseRepository::class)->findOwnedBy($exerciseId, $userId);

    expect($exercise)->not->toBeNull()
        ->and($exercise->name->value)->toBe('トレッドミルランニング')
        ->and($exercise->equipmentId?->value)->toBe($equipment->id)
        ->and($exercise->primaryTarget)->toBe(BodyPart::Calves)
        ->and($exercise->secondaryTarget)->toBe(BodyPart::Core)
        ->and($exercise->recordingMethod)->toBe(RecordingMethod::DurationAndDistance);
});

it('別ユーザーの種目を更新しない', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownerId = UserId::fromString($owner->id);
    $exerciseId = app(CreateExercise::class)->handle(
        userId: $ownerId,
        name: 'レッグプレス',
        equipmentId: null,
        primaryTarget: BodyPart::Quadriceps,
        secondaryTarget: null,
        recordingMethod: RecordingMethod::WeightAndRepetitions,
    );

    $updated = app(UpdateExercise::class)->handle(
        exerciseId: $exerciseId,
        userId: UserId::fromString($otherUser->id),
        name: '変更後',
        equipmentId: null,
        primaryTarget: BodyPart::Chest,
        secondaryTarget: null,
    );

    expect($updated)->toBeFalse();

    $exercise = app(ExerciseRepository::class)->findOwnedBy($exerciseId, $ownerId);

    expect($exercise)->not->toBeNull()
        ->and($exercise->name->value)->toBe('レッグプレス')
        ->and($exercise->primaryTarget)->toBe(BodyPart::Quadriceps);
});

it('別ユーザーの機材へ種目を変更しない', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherUsersEquipment = Equipment::factory()->forUser($otherUser)->create();
    $userId = UserId::fromString($user->id);
    $exerciseId = app(CreateExercise::class)->handle(
        userId: $userId,
        name: 'レッグプレス',
        equipmentId: null,
        primaryTarget: BodyPart::Quadriceps,
        secondaryTarget: null,
        recordingMethod: RecordingMethod::WeightAndRepetitions,
    );

    $updated = app(UpdateExercise::class)->handle(
        exerciseId: $exerciseId,
        userId: $userId,
        name: '変更後',
        equipmentId: EquipmentId::fromString($otherUsersEquipment->id),
        primaryTarget: BodyPart::Glutes,
        secondaryTarget: null,
    );

    expect($updated)->toBeFalse();

    $exercise = app(ExerciseRepository::class)->findOwnedBy($exerciseId, $userId);

    expect($exercise)->not->toBeNull()
        ->and($exercise->name->value)->toBe('レッグプレス')
        ->and($exercise->equipmentId)->toBeNull()
        ->and($exercise->primaryTarget)->toBe(BodyPart::Quadriceps);
});
