<?php

declare(strict_types=1);

use Domain\Equipment\Equipment;
use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\EquipmentName;
use Domain\Equipment\Exceptions\InvalidEquipmentNameException;
use Domain\Equipment\Exceptions\InvalidEquipmentOwnerException;
use Domain\Equipment\Exceptions\InvalidWeightIncrementException;
use Domain\Equipment\WeightIncrement;
use Domain\Equipment\WeightUnit;

it('機材を所有者と設定値から定義できる', function () {
    $equipment = new Equipment(
        userId: 1,
        name: EquipmentName::fromString(' レッグプレス '),
        category: EquipmentCategory::Machine,
        weightUnit: WeightUnit::Kilogram,
        weightIncrement: WeightIncrement::fromDecimal('9'),
    );

    expect($equipment->name->value)->toBe('レッグプレス')
        ->and($equipment->weightIncrement->toDecimal())->toBe('9.00');
});

it('小数2桁までの正の重量増分を受け入れる', function (string $input, string $expected) {
    expect(WeightIncrement::fromDecimal($input)->toDecimal())->toBe($expected);
})->with([
    ['0.01', '0.01'],
    ['2.5', '2.50'],
    ['999.99', '999.99'],
]);

it('不正な重量増分を拒否する', function (string $input) {
    WeightIncrement::fromDecimal($input);
})->with(['0', '-1', '1.001', '1000', 'abc'])
    ->throws(InvalidWeightIncrementException::class);

it('空または長すぎる機材名を拒否する', function (string $input) {
    EquipmentName::fromString($input);
})->with(['   ', str_repeat('あ', 101)])
    ->throws(InvalidEquipmentNameException::class);

it('不正な所有者IDを拒否する', function () {
    new Equipment(
        userId: 0,
        name: EquipmentName::fromString('レッグプレス'),
        category: EquipmentCategory::Machine,
        weightUnit: WeightUnit::Kilogram,
        weightIncrement: WeightIncrement::fromDecimal('9'),
    );
})->throws(InvalidEquipmentOwnerException::class);
