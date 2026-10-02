<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\User;
use Infra\Persistence\Eloquent\Models\Workout;

it('同じ種目は一つのトレーニングへ一度だけ追加できる', function () {
    $user = User::factory()->create();
    $workout = Workout::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->forUser($user)->create();

    insertWorkoutExercise($workout->id, $exercise->id, 1);

    insertWorkoutExercise($workout->id, $exercise->id, 2);
})->throws(QueryException::class);

it('一つのトレーニングで追加順を重複させない', function () {
    $user = User::factory()->create();
    $workout = Workout::factory()->forUser($user)->create();
    $firstExercise = Exercise::factory()->forUser($user)->create();
    $secondExercise = Exercise::factory()->forUser($user)->create();

    insertWorkoutExercise($workout->id, $firstExercise->id, 1);

    insertWorkoutExercise($workout->id, $secondExercise->id, 1);
})->throws(QueryException::class);

it('トレーニングを削除すると追加済み種目も削除する', function () {
    $user = User::factory()->create();
    $workout = Workout::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->forUser($user)->create();
    insertWorkoutExercise($workout->id, $exercise->id, 1);

    $workout->delete();

    $this->assertDatabaseCount('workout_exercises', 0);
});

it('ユーザーを削除すると追加済み種目も削除する', function () {
    $user = User::factory()->create();
    $workout = Workout::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->forUser($user)->create();
    insertWorkoutExercise($workout->id, $exercise->id, 1);

    $user->delete();

    $this->assertDatabaseCount('workout_exercises', 0);
});

function insertWorkoutExercise(string $workoutId, string $exerciseId, int $position): void
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
