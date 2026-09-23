<?php

declare(strict_types=1);

use App\Service\Query\Equipment\Dto\EquipmentListItemCollection;
use App\Service\Query\Equipment\Dto\EquipmentListItemDto;

function equipmentListItemDto(string $id, string $name): EquipmentListItemDto
{
    return new EquipmentListItemDto(
        id: $id,
        name: $name,
        category: 'machine',
        weightUnit: 'kg',
        weightIncrement: '5.00',
    );
}

it('DTOを反復し件数と空状態を返す', function () {
    $first = equipmentListItemDto('1', 'レッグプレス');
    $second = equipmentListItemDto('2', 'チェストプレス');

    $items = new EquipmentListItemCollection([$first, $second]);

    expect($items)->toHaveCount(2)
        ->and($items->isEmpty())->toBeFalse()
        ->and($items[0])->toBe($first)
        ->and(iterator_to_array($items))->toBe([$first, $second]);
});

it('条件に一致する最初のDTOを返す', function () {
    $first = equipmentListItemDto('1', 'レッグプレス');
    $second = equipmentListItemDto('2', 'チェストプレス');
    $items = new EquipmentListItemCollection([$first, $second]);

    $found = $items->find(fn (EquipmentListItemDto $item): bool => $item->id === '2');

    expect($found)->toBe($second)
        ->and($items->find(fn (): bool => false))->toBeNull();
});

it('異なる型の要素を拒否する', function () {
    new EquipmentListItemCollection([new stdClass]);
})->throws(InvalidArgumentException::class);

it('存在しない位置の参照を拒否する', function () {
    $items = new EquipmentListItemCollection([]);

    $items[0];
})->throws(OutOfBoundsException::class);

it('要素の変更を拒否する', function () {
    $items = new EquipmentListItemCollection([]);

    $items[] = equipmentListItemDto('1', 'レッグプレス');
})->throws(LogicException::class);
