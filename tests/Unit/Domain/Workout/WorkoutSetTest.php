<?php

declare(strict_types=1);

use Domain\Exercise\ExerciseId;
use Domain\Workout\Exceptions\InvalidWorkoutSetPositionException;
use Domain\Workout\Exceptions\InvalidWorkoutSetRepetitionsException;
use Domain\Workout\Exceptions\InvalidWorkoutSetWeightException;
use Domain\Workout\WorkoutId;
use Domain\Workout\WorkoutSet;
use Domain\Workout\WorkoutSetId;
use Domain\Workout\WorkoutSetPosition;
use Domain\Workout\WorkoutSetRepetitions;
use Domain\Workout\WorkoutSetWeight;

it('重量と回数の完了セットを定義できる', function () {
    $completedAt = new DateTimeImmutable('2026-10-03 10:30:00');

    $set = new WorkoutSet(
        id: WorkoutSetId::fromString('01990000-0000-7000-8000-000000000003'),
        workoutId: WorkoutId::fromString('01990000-0000-7000-8000-000000000001'),
        exerciseId: ExerciseId::fromString('01990000-0000-7000-8000-000000000002'),
        position: WorkoutSetPosition::fromInt(1),
        weight: WorkoutSetWeight::fromDecimal('36'),
        repetitions: WorkoutSetRepetitions::fromInt(10),
        completedAt: $completedAt,
    );

    expect($set->position->value)->toBe(1)
        ->and($set->weight->toDecimal())->toBe('36.00')
        ->and($set->repetitions->value)->toBe(10)
        ->and($set->completedAt)->toBe($completedAt);
});

it('小数2桁までの重量を受け入れる', function (string $input, string $expected) {
    expect(WorkoutSetWeight::fromDecimal($input)->toDecimal())->toBe($expected);
})->with([
    '自重' => ['0', '0.00'],
    '小数1桁' => [' 2.5 ', '2.50'],
    '最大値' => ['9999.99', '9999.99'],
]);

it('範囲または形式が不正な重量を拒否する', function (string $input) {
    WorkoutSetWeight::fromDecimal($input);
})->with(['', '-0.01', '.5', '1.', '1.001', '10000', '01', 'abc'])
    ->throws(InvalidWorkoutSetWeightException::class);

it('1回から999回までを受け入れる', function (int $input) {
    expect(WorkoutSetRepetitions::fromInt($input)->value)->toBe($input);
})->with([1, 10, 999]);

it('範囲外の回数を拒否する', function (int $input) {
    WorkoutSetRepetitions::fromInt($input);
})->with([-1, 0, 1000])
    ->throws(InvalidWorkoutSetRepetitionsException::class);

it('保存可能なセット順を受け入れる', function (int $input) {
    expect(WorkoutSetPosition::fromInt($input)->value)->toBe($input);
})->with([1, 65_535]);

it('範囲外のセット順を拒否する', function (int $input) {
    WorkoutSetPosition::fromInt($input);
})->with([0, 65_536])
    ->throws(InvalidWorkoutSetPositionException::class);
