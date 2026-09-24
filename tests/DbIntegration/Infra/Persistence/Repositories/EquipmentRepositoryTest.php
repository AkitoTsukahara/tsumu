<?php

declare(strict_types=1);

use App\Service\Command\CreateEquipment;
use App\Service\Command\UpdateEquipment;
use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\EquipmentId;
use Domain\Equipment\EquipmentRepository;
use Domain\Equipment\WeightUnit;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Equipment as EquipmentModel;
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

it('更新Commandで自分の機材を更新できる', function () {
    $user = User::factory()->create();
    $userId = UserId::fromString($user->id);
    $equipmentId = app(CreateEquipment::class)->handle(
        userId: $userId,
        name: 'レッグプレス',
        category: EquipmentCategory::Machine,
        weightUnit: WeightUnit::Kilogram,
        weightIncrement: '9',
    );

    $updated = app(UpdateEquipment::class)->handle(
        equipmentId: $equipmentId,
        userId: $userId,
        name: ' レッグプレス45 ',
        category: EquipmentCategory::FreeWeight,
        weightUnit: WeightUnit::Kilogram,
        weightIncrement: '4.5',
    );

    $equipment = app(EquipmentRepository::class)->findOwnedBy($equipmentId, $userId);

    expect($updated)->toBeTrue()
        ->and($equipment)->not->toBeNull()
        ->and($equipment->name->value)->toBe('レッグプレス45')
        ->and($equipment->category)->toBe(EquipmentCategory::FreeWeight)
        ->and($equipment->weightUnit)->toBe(WeightUnit::Kilogram)
        ->and($equipment->weightIncrement->toDecimal())->toBe('4.50');
});

it('更新Commandで別ユーザーの機材を変更できない', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $equipment = EquipmentModel::factory()
        ->forUser($owner)
        ->create(['name' => 'レッグプレス']);

    $updated = app(UpdateEquipment::class)->handle(
        equipmentId: EquipmentId::fromString($equipment->id),
        userId: UserId::fromString($otherUser->id),
        name: '変更後',
        category: EquipmentCategory::Machine,
        weightUnit: WeightUnit::Kilogram,
        weightIncrement: '5',
    );

    expect($updated)->toBeFalse();
    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'user_id' => $owner->id,
        'name' => 'レッグプレス',
    ]);
});
