<?php

declare(strict_types=1);

use Domain\Exercise\BodyPart;
use Domain\Exercise\RecordingMethod;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\User;

it('種目をユーザーと任意の機材に紐づけて保存できる', function () {
    $equipment = Equipment::factory()->create();

    $exercise = Exercise::factory()->forEquipment($equipment)->create([
        'name' => 'レッグプレス',
    ]);

    expect($exercise->user_id)->toBe($equipment->user_id)
        ->and($exercise->equipment_id)->toBe($equipment->id)
        ->and($exercise->primary_target->value)->toBe('quadriceps')
        ->and($exercise->recording_method->value)->toBe('weight_repetitions');
});

it('機材を使わない種目を保存できる', function () {
    $exercise = Exercise::factory()->create([
        'equipment_id' => null,
        'secondary_target' => null,
    ]);

    expect($exercise->equipment_id)->toBeNull()
        ->and($exercise->secondary_target)->toBeNull();
});

it('Domainで定義した対象部位を保存できる', function (BodyPart $bodyPart) {
    $exercise = Exercise::factory()->create([
        'primary_target' => $bodyPart,
    ]);

    expect($exercise->primary_target)->toBe($bodyPart);
})->with([
    '胸' => BodyPart::Chest,
    '背中' => BodyPart::Back,
    '肩' => BodyPart::Shoulders,
    '上腕二頭筋' => BodyPart::Biceps,
    '上腕三頭筋' => BodyPart::Triceps,
    '前腕' => BodyPart::Forearms,
    '大腿四頭筋' => BodyPart::Quadriceps,
    'ハムストリング' => BodyPart::Hamstrings,
    '臀部' => BodyPart::Glutes,
    'ふくらはぎ' => BodyPart::Calves,
    '体幹' => BodyPart::Core,
    '全身' => BodyPart::FullBody,
    'その他' => BodyPart::Other,
]);

it('Domainで定義した記録方式を保存できる', function (RecordingMethod $recordingMethod) {
    $exercise = Exercise::factory()->create([
        'recording_method' => $recordingMethod,
    ]);

    expect($exercise->recording_method)->toBe($recordingMethod);
})->with([
    '重量と回数' => RecordingMethod::WeightAndRepetitions,
    '時間' => RecordingMethod::Duration,
    '時間と距離' => RecordingMethod::DurationAndDistance,
    '速度と時間' => RecordingMethod::SpeedAndDuration,
]);

it('使用機材を削除しても種目を残して紐づけだけを解除する', function () {
    $equipment = Equipment::factory()->create();
    $exercise = Exercise::factory()->forEquipment($equipment)->create();

    $equipment->delete();

    expect($exercise->fresh())->not->toBeNull()
        ->and($exercise->fresh()?->equipment_id)->toBeNull();
});

it('ユーザーを削除すると所有する種目も削除する', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->forUser($user)->create();

    $user->delete();

    $this->assertDatabaseMissing('exercises', ['id' => $exercise->id]);
});

it('定義されていない対象部位をDB制約で拒否する', function () {
    insertExercise(['primary_target' => 'unknown']);
})->throws(QueryException::class);

it('定義されていない記録方式をDB制約で拒否する', function () {
    insertExercise(['recording_method' => 'unknown']);
})->throws(QueryException::class);

/** @param array<string, string|null> $overrides */
function insertExercise(array $overrides = []): void
{
    $user = User::factory()->create();

    DB::table('exercises')->insert(array_merge([
        'id' => (string) Str::uuid7(),
        'user_id' => $user->id,
        'equipment_id' => null,
        'name' => 'レッグプレス',
        'primary_target' => 'quadriceps',
        'secondary_target' => 'glutes',
        'recording_method' => 'weight_repetitions',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}
