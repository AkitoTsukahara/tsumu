<?php

declare(strict_types=1);

use App\Service\Query\Workout\WorkoutExerciseListQuery;
use Domain\User\UserId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\User;
use Infra\Persistence\Eloquent\Models\Workout;

it('進行中トレーニングの追加済み種目と追加可能な自分の種目を返す', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $workout = Workout::factory()->forUser($user)->create();
    $equipment = Equipment::factory()->forUser($user)->create(['name' => 'レッグプレス機材']);
    $firstAdded = Exercise::factory()->forUser($user)->create(['name' => 'スクワット']);
    $secondAdded = Exercise::factory()->forEquipment($equipment)->create(['name' => 'レッグプレス']);
    $available = Exercise::factory()->forUser($user)->create(['name' => 'プランク']);
    Exercise::factory()->forUser($otherUser)->create(['name' => '他ユーザーの種目']);
    insertWorkoutExerciseForQuery($workout->id, $firstAdded->id, 2);
    insertWorkoutExerciseForQuery($workout->id, $secondAdded->id, 1);

    $items = app(WorkoutExerciseListQuery::class)->addedForUser(UserId::fromString($user->id));
    $availableItems = app(WorkoutExerciseListQuery::class)->availableForUser(UserId::fromString($user->id));

    expect($items)->toHaveCount(2)
        ->and($items[0]->name)->toBe('レッグプレス')
        ->and($items[0]->equipmentName)->toBe('レッグプレス機材')
        ->and($items[1]->name)->toBe('スクワット')
        ->and($availableItems)->toHaveCount(1)
        ->and($availableItems[0]->id)->toBe($available->id);
});

it('終了済みや別ユーザーのトレーニングに追加した種目を混ぜない', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Workout::factory()->forUser($user)->create();
    $completedWorkout = Workout::factory()->forUser($user)->completed()->create();
    $otherWorkout = Workout::factory()->forUser($otherUser)->create();
    $exercise = Exercise::factory()->forUser($user)->create();
    $otherExercise = Exercise::factory()->forUser($otherUser)->create();
    insertWorkoutExerciseForQuery($completedWorkout->id, $exercise->id, 1);
    insertWorkoutExerciseForQuery($otherWorkout->id, $otherExercise->id, 1);

    $items = app(WorkoutExerciseListQuery::class)->addedForUser(UserId::fromString($user->id));
    $availableItems = app(WorkoutExerciseListQuery::class)->availableForUser(UserId::fromString($user->id));

    expect($items)->toBeEmpty()
        ->and($availableItems)->toHaveCount(1)
        ->and($availableItems[0]->id)->toBe($exercise->id);
});

function insertWorkoutExerciseForQuery(string $workoutId, string $exerciseId, int $position): void
{
    DB::table('workout_exercises')->insert([
        'id' => (string) Str::uuid7(),
        'workout_id' => $workoutId,
        'exercise_id' => $exerciseId,
        'position' => $position,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
