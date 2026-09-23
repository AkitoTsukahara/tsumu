<?php

declare(strict_types=1);

use App\Service\Query\EquipmentListQuery;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\User;

it('自分の機材だけを名前とIDの順で返す', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Equipment::factory()->forUser($user)->create([
        'id' => '01990000-0000-7000-8000-000000000002',
        'name' => 'レッグプレス',
        'weight_increment' => '9.00',
    ]);
    Equipment::factory()->forUser($user)->create([
        'id' => '01990000-0000-7000-8000-000000000001',
        'name' => 'アームカール',
    ]);
    Equipment::factory()->forUser($otherUser)->create(['name' => '他ユーザーの機材']);

    $items = app(EquipmentListQuery::class)->forUser(UserId::fromString($user->id));

    expect($items)->toHaveCount(2)
        ->and($items[0]->id)->toBe('01990000-0000-7000-8000-000000000001')
        ->and($items[0]->name)->toBe('アームカール')
        ->and($items[0]->category)->toBe('machine')
        ->and($items[0]->weightUnit)->toBe('kg')
        ->and($items[1]->name)->toBe('レッグプレス')
        ->and($items[1]->weightIncrement)->toBe('9.00');
});
