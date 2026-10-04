<?php

declare(strict_types=1);

use App\Service\Command\RecordWorkoutSet;
use Domain\Exercise\ExerciseId;
use Domain\Exercise\RecordingMethod;
use Domain\User\UserId;
use Domain\Workout\WorkoutId;
use Domain\Workout\WorkoutSet;
use Domain\Workout\WorkoutSetId;
use Domain\Workout\WorkoutSetPosition;
use Domain\Workout\WorkoutSetRepetitions;
use Domain\Workout\WorkoutSetRepository;
use Domain\Workout\WorkoutSetWeight;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\User;
use Infra\Persistence\Eloquent\Models\Workout;
use Infra\Persistence\Eloquent\Models\WorkoutSet as WorkoutSetModel;

use function Pest\Laravel\travelTo;

it('進行中トレーニングの追加済み種目へ完了セットを保存して再取得できる', function () {
    $user = User::factory()->create();
    [$workout, $exercise] = createWorkoutExerciseForRecording($user);

    $workoutSetId = travelTo(
        '2026-10-04 10:30:00',
        fn () => app(RecordWorkoutSet::class)->handle(
            UserId::fromString($user->id),
            ExerciseId::fromString($exercise->id),
            '36',
            10,
        ),
    );

    expect($workoutSetId)->not->toBeNull();

    $workoutSet = app(WorkoutSetRepository::class)->findOwnedBy(
        $workoutSetId,
        UserId::fromString($user->id),
    );

    expect($workoutSet)->not->toBeNull()
        ->and($workoutSet->workoutId->value)->toBe($workout->id)
        ->and($workoutSet->exerciseId->value)->toBe($exercise->id)
        ->and($workoutSet->position->value)->toBe(1)
        ->and($workoutSet->weight->toDecimal())->toBe('36.00')
        ->and($workoutSet->repetitions->value)->toBe(10)
        ->and($workoutSet->completedAt->format('Y-m-d H:i:s'))->toBe('2026-10-04 10:30:00');
});

it('同じ種目のセットを実施順に保存する', function () {
    $user = User::factory()->create();
    [$workout, $exercise] = createWorkoutExerciseForRecording($user);
    $command = app(RecordWorkoutSet::class);
    $userId = UserId::fromString($user->id);
    $exerciseId = ExerciseId::fromString($exercise->id);

    $command->handle($userId, $exerciseId, '36', 10);
    $command->handle($userId, $exerciseId, '45', 8);

    $sets = DB::table('workout_sets')
        ->where('workout_id', $workout->id)
        ->where('exercise_id', $exercise->id)
        ->orderBy('position')
        ->get();

    expect($sets)->toHaveCount(2)
        ->and($sets[0]->position)->toBe(1)
        ->and($sets[0]->weight)->toBe(36)
        ->and($sets[1]->position)->toBe(2)
        ->and($sets[1]->weight)->toBe(45);
});

it('追加していない種目へセットを保存しない', function () {
    $user = User::factory()->create();
    Workout::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->forUser($user)->create();

    $workoutSetId = app(RecordWorkoutSet::class)->handle(
        UserId::fromString($user->id),
        ExerciseId::fromString($exercise->id),
        '36',
        10,
    );

    expect($workoutSetId)->toBeNull();
    $this->assertDatabaseCount('workout_sets', 0);
});

it('終了済みトレーニングへセットを保存しない', function () {
    $user = User::factory()->create();
    [, $exercise] = createWorkoutExerciseForRecording($user, completed: true);

    $workoutSetId = app(RecordWorkoutSet::class)->handle(
        UserId::fromString($user->id),
        ExerciseId::fromString($exercise->id),
        '36',
        10,
    );

    expect($workoutSetId)->toBeNull();
    $this->assertDatabaseCount('workout_sets', 0);
});

it('重量と回数以外の記録方式にはセットを保存しない', function () {
    $user = User::factory()->create();
    [, $exercise] = createWorkoutExerciseForRecording(
        $user,
        recordingMethod: RecordingMethod::Duration,
    );

    $workoutSetId = app(RecordWorkoutSet::class)->handle(
        UserId::fromString($user->id),
        ExerciseId::fromString($exercise->id),
        '36',
        10,
    );

    expect($workoutSetId)->toBeNull();
    $this->assertDatabaseCount('workout_sets', 0);
});

it('別ユーザーが所有する種目へセットを保存しない', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Workout::factory()->forUser($user)->create();
    [, $otherExercise] = createWorkoutExerciseForRecording($otherUser);

    $workoutSetId = app(RecordWorkoutSet::class)->handle(
        UserId::fromString($user->id),
        ExerciseId::fromString($otherExercise->id),
        '36',
        10,
    );

    expect($workoutSetId)->toBeNull();
    $this->assertDatabaseCount('workout_sets', 0);
});

it('別ユーザーの完了セットを再取得しない', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    [$workout, $exercise] = createWorkoutExerciseForRecording($owner);
    $workoutSet = WorkoutSetModel::factory()
        ->forWorkoutExercise($workout, $exercise)
        ->create();

    $foundWorkoutSet = app(WorkoutSetRepository::class)->findOwnedBy(
        WorkoutSetId::fromString($workoutSet->id),
        UserId::fromString($otherUser->id),
    );

    expect($foundWorkoutSet)->toBeNull();
});

it('Repositoryでも終了済みトレーニングへの保存を拒否する', function () {
    $user = User::factory()->create();
    [$workout, $exercise] = createWorkoutExerciseForRecording($user, completed: true);

    $saved = app(WorkoutSetRepository::class)->saveToInProgressWorkout(
        makeWorkoutSetForRecording($workout, $exercise),
        UserId::fromString($user->id),
    );

    expect($saved)->toBeFalse();
    $this->assertDatabaseCount('workout_sets', 0);
});

it('Repositoryでも別ユーザーのトレーニングへの保存を拒否する', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    [$workout, $exercise] = createWorkoutExerciseForRecording($owner);

    $saved = app(WorkoutSetRepository::class)->saveToInProgressWorkout(
        makeWorkoutSetForRecording($workout, $exercise),
        UserId::fromString($otherUser->id),
    );

    expect($saved)->toBeFalse();
    $this->assertDatabaseCount('workout_sets', 0);
});

/** @return array{Workout, Exercise} */
function createWorkoutExerciseForRecording(
    User $user,
    bool $completed = false,
    RecordingMethod $recordingMethod = RecordingMethod::WeightAndRepetitions,
): array {
    $workoutFactory = Workout::factory()->forUser($user);
    $workout = $completed ? $workoutFactory->completed()->create() : $workoutFactory->create();
    $exercise = Exercise::factory()->forUser($user)->create([
        'recording_method' => $recordingMethod,
    ]);

    DB::table('workout_exercises')->insert([
        'id' => (string) Str::uuid7(),
        'workout_id' => $workout->id,
        'exercise_id' => $exercise->id,
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [$workout, $exercise];
}

function makeWorkoutSetForRecording(Workout $workout, Exercise $exercise): WorkoutSet
{
    return new WorkoutSet(
        id: WorkoutSetId::fromString((string) Str::uuid7()),
        workoutId: WorkoutId::fromString($workout->id),
        exerciseId: ExerciseId::fromString($exercise->id),
        position: WorkoutSetPosition::fromInt(1),
        weight: WorkoutSetWeight::fromDecimal('36'),
        repetitions: WorkoutSetRepetitions::fromInt(10),
        completedAt: now()->toDateTimeImmutable(),
    );
}
