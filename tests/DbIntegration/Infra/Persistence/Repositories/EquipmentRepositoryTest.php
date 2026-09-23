<?php

declare(strict_types=1);

use App\Service\Command\CreateEquipment;
use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\EquipmentRepository;
use Domain\Equipment\WeightUnit;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\User;

it('登録Commandで機材を保存してRepositoryから再取得できる', function () {
    $user = User::factory()->create();
    $userId = UserId::fromString($user->id);

    $equipmentId = app(CreateEquipment::class)->handle(
        userId: $userId,
        name: ' レッグプレス ',
        category: EquipmentCategory::Machine,
        weightUnit: WeightUnit::Kilogram,
        weightIncrement: '9',
    );

    $equipment = app(EquipmentRepository::class)->findOwnedBy($equipmentId, $userId);

    expect($equipment)->not->toBeNull()
        ->and($equipment->id->value)->toBe($equipmentId->value)
        ->and($equipment->userId->value)->toBe($user->id)
        ->and($equipment->name->value)->toBe('レッグプレス')
        ->and($equipment->category)->toBe(EquipmentCategory::Machine)
        ->and($equipment->weightUnit)->toBe(WeightUnit::Kilogram)
        ->and($equipment->weightIncrement->toDecimal())->toBe('9.00');
});

it('別ユーザーが所有する機材は再取得しない', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $equipmentId = app(CreateEquipment::class)->handle(
        userId: UserId::fromString($owner->id),
        name: 'レッグプレス',
        category: EquipmentCategory::Machine,
        weightUnit: WeightUnit::Kilogram,
        weightIncrement: '9',
    );

    $equipment = app(EquipmentRepository::class)->findOwnedBy(
        $equipmentId,
        UserId::fromString($otherUser->id),
    );

    expect($equipment)->toBeNull();
});
