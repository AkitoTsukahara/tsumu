<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\User;
use Infra\Persistence\Eloquent\Models\Workout;

it('追加済み種目へ完了セットを保存できる', function () {
    [$workoutId, $exerciseId] = createWorkoutExerciseForSet();
    $setId = (string) Str::uuid7();

    insertWorkoutSet($setId, $workoutId, $exerciseId, 1);

    $this->assertDatabaseHas('workout_sets', [
        'id' => $setId,
        'workout_id' => $workoutId,
        'exercise_id' => $exerciseId,
        'position' => 1,
        'weight' => 36,
        'repetitions' => 10,
    ]);
    expect(Str::isUuid($setId, version: 7))->toBeTrue();
});

it('同じ種目内でセット順を重複させない', function () {
    [$workoutId, $exerciseId] = createWorkoutExerciseForSet();
    insertWorkoutSet((string) Str::uuid7(), $workoutId, $exerciseId, 1);

    insertWorkoutSet((string) Str::uuid7(), $workoutId, $exerciseId, 1);
})->throws(QueryException::class);

it('トレーニングへ追加していない種目のセットを拒否する', function () {
    $user = User::factory()->create();
    $workout = Workout::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->forUser($user)->create();

    insertWorkoutSet((string) Str::uuid7(), $workout->id, $exercise->id, 1);
})->throws(QueryException::class);

it('トレーニングを削除すると完了セットも削除する', function () {
    [$workoutId, $exerciseId] = createWorkoutExerciseForSet();
    insertWorkoutSet((string) Str::uuid7(), $workoutId, $exerciseId, 1);

    DB::table('workouts')->where('id', $workoutId)->delete();

    $this->assertDatabaseCount('workout_sets', 0);
});

/** @return array{string, string} */
function createWorkoutExerciseForSet(): array
{
    $user = User::factory()->create();
    $workout = Workout::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->forUser($user)->create();

    DB::table('workout_exercises')->insert([
        'id' => (string) Str::uuid7(),
        'workout_id' => $workout->id,
        'exercise_id' => $exercise->id,
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [$workout->id, $exercise->id];
}

function insertWorkoutSet(
    string $id,
    string $workoutId,
    string $exerciseId,
    int $position,
): void {
    DB::table('workout_sets')->insert([
        'id' => $id,
        'workout_id' => $workoutId,
        'exercise_id' => $exerciseId,
        'position' => $position,
        'weight' => '36.00',
        'repetitions' => 10,
        'completed_at' => '2026-10-03 10:30:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
