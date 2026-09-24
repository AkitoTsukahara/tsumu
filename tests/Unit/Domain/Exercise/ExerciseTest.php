<?php

declare(strict_types=1);

use Domain\Equipment\EquipmentId;
use Domain\Exercise\BodyPart;
use Domain\Exercise\Exceptions\InvalidExerciseNameException;
use Domain\Exercise\Exercise;
use Domain\Exercise\ExerciseId;
use Domain\Exercise\ExerciseName;
use Domain\Exercise\RecordingMethod;
use Domain\User\UserId;

it('種目を所有者と任意の機材と記録設定から定義できる', function () {
    $exercise = new Exercise(
        id: ExerciseId::fromString('01990000-0000-7000-8000-000000000002'),
        userId: UserId::fromString('01990000-0000-7000-8000-000000000000'),
        name: ExerciseName::fromString(' レッグプレス '),
        equipmentId: EquipmentId::fromString('01990000-0000-7000-8000-000000000001'),
        primaryTarget: BodyPart::Quadriceps,
        secondaryTarget: BodyPart::Glutes,
        recordingMethod: RecordingMethod::WeightAndRepetitions,
    );

    expect($exercise->name->value)->toBe('レッグプレス')
        ->and($exercise->primaryTarget)->toBe(BodyPart::Quadriceps)
        ->and($exercise->secondaryTarget)->toBe(BodyPart::Glutes);
});

it('機材と副対象部位を指定せずに種目を定義できる', function () {
    $exercise = new Exercise(
        id: ExerciseId::fromString('01990000-0000-7000-8000-000000000002'),
        userId: UserId::fromString('01990000-0000-7000-8000-000000000000'),
        name: ExerciseName::fromString('スクワット'),
        equipmentId: null,
        primaryTarget: BodyPart::Quadriceps,
        secondaryTarget: null,
        recordingMethod: RecordingMethod::WeightAndRepetitions,
    );

    expect($exercise->equipmentId)->toBeNull()
        ->and($exercise->secondaryTarget)->toBeNull();
});

it('空または長すぎる種目名を拒否する', function (string $input) {
    ExerciseName::fromString($input);
})->with(['   ', str_repeat('あ', 101)])
    ->throws(InvalidExerciseNameException::class);

it('合意した記録方式を定義している', function () {
    expect(array_column(RecordingMethod::cases(), 'value'))->toBe([
        'weight_repetitions',
        'duration',
        'duration_distance',
        'speed_duration',
    ]);
});

it('合意した対象部位を定義している', function () {
    expect(array_column(BodyPart::cases(), 'value'))->toBe([
        'chest',
        'back',
        'shoulders',
        'biceps',
        'triceps',
        'forearms',
        'quadriceps',
        'hamstrings',
        'glutes',
        'calves',
        'core',
        'full_body',
        'other',
    ]);
});
