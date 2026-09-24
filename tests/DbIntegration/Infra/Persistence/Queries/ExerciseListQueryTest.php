<?php

declare(strict_types=1);

use App\Service\Query\Exercise\ExerciseListQuery;
use Domain\Exercise\BodyPart;
use Domain\Exercise\RecordingMethod;
use Domain\User\UserId;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\User;

it('自分の種目だけを名前とIDの順で機材名とともに返す', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $equipment = Equipment::factory()->forUser($user)->create(['name' => 'レッグプレス機材']);
    Exercise::factory()->forEquipment($equipment)->create([
        'id' => '01990000-0000-7000-8000-000000000002',
        'name' => 'レッグプレス',
        'primary_target' => BodyPart::Quadriceps,
        'secondary_target' => BodyPart::Glutes,
        'recording_method' => RecordingMethod::WeightAndRepetitions,
    ]);
    Exercise::factory()->forUser($user)->create([
        'id' => '01990000-0000-7000-8000-000000000001',
        'name' => 'プランク',
        'equipment_id' => null,
        'primary_target' => BodyPart::Core,
        'secondary_target' => null,
        'recording_method' => RecordingMethod::Duration,
    ]);
    Exercise::factory()->forUser($otherUser)->create(['name' => '他ユーザーの種目']);

    $items = app(ExerciseListQuery::class)->forUser(UserId::fromString($user->id));

    expect($items)->toHaveCount(2)
        ->and($items[0]->id)->toBe('01990000-0000-7000-8000-000000000001')
        ->and($items[0]->name)->toBe('プランク')
        ->and($items[0]->equipmentName)->toBeNull()
        ->and($items[0]->primaryTarget)->toBe('core')
        ->and($items[0]->secondaryTarget)->toBeNull()
        ->and($items[0]->recordingMethod)->toBe('duration')
        ->and($items[1]->name)->toBe('レッグプレス')
        ->and($items[1]->equipmentId)->toBe($equipment->id)
        ->and($items[1]->equipmentName)->toBe('レッグプレス機材')
        ->and($items[1]->primaryTarget)->toBe('quadriceps')
        ->and($items[1]->secondaryTarget)->toBe('glutes')
        ->and($items[1]->recordingMethod)->toBe('weight_repetitions');
});

it('所有者が異なる機材名を種目一覧へ公開しない', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherUsersEquipment = Equipment::factory()->forUser($otherUser)->create([
        'name' => '他ユーザーの秘密の機材',
    ]);
    Exercise::factory()->forUser($user)->create([
        'name' => '所有者不整合の種目',
        'equipment_id' => $otherUsersEquipment->id,
    ]);

    $items = app(ExerciseListQuery::class)->forUser(UserId::fromString($user->id));

    expect($items)->toHaveCount(1)
        ->and($items[0]->equipmentId)->toBeNull()
        ->and($items[0]->equipmentName)->toBeNull();
});
