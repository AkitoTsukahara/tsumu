<?php

namespace App\Livewire\Workout;

use App\Service\Command\AddExerciseToWorkout;
use App\Service\Query\Workout\InProgressWorkoutQuery;
use App\Service\Query\Workout\WorkoutExerciseListQuery;
use Domain\Exercise\ExerciseId;
use Domain\Shared\Exceptions\InvalidUuidV7IdException;
use Domain\User\UserId;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Workout | Tsumu')]
final class Index extends Component
{
    #[Locked]
    public string $startedAtLabel = '';

    public string $selectedExerciseId = '';

    public function mount(InProgressWorkoutQuery $inProgressWorkoutQuery): void
    {
        $workout = $inProgressWorkoutQuery->forUser($this->authenticatedUserId());

        if ($workout === null) {
            $this->redirectRoute('today');

            return;
        }

        $this->startedAtLabel = $workout->startedAt->format('Y年n月j日 H:i');
    }

    public function addExercise(AddExerciseToWorkout $addExerciseToWorkout): void
    {
        $this->validate([
            'selectedExerciseId' => ['required', 'uuid'],
        ], [
            'selectedExerciseId.required' => '追加する種目を選択してください。',
            'selectedExerciseId.uuid' => '追加する種目を選択肢から選んでください。',
        ]);

        try {
            $exerciseId = ExerciseId::fromString($this->selectedExerciseId);
        } catch (InvalidUuidV7IdException) {
            abort(404);
        }

        abort_unless($addExerciseToWorkout->handle($this->authenticatedUserId(), $exerciseId), 404);

        $this->reset('selectedExerciseId');
        session()->flash('status', '種目を追加しました。');
    }

    public function render(WorkoutExerciseListQuery $workoutExerciseList): View
    {
        $userId = $this->authenticatedUserId();

        return view('livewire.workout.index', [
            'exerciseItems' => $workoutExerciseList->addedForUser($userId),
            'availableExerciseItems' => $workoutExerciseList->availableForUser($userId),
        ]);
    }

    private function authenticatedUserId(): UserId
    {
        return UserId::fromString((string) Auth::id());
    }
}
