<?php

namespace App\Livewire\Exercise;

use App\Livewire\Forms\ExerciseForm;
use App\Service\Command\CreateExercise;
use App\Service\Query\Equipment\EquipmentListQuery;
use App\Service\Query\Exercise\ExerciseListQuery;
use Domain\Exercise\RecordingMethod;
use Domain\User\UserId;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('種目 | Tsumu')]
final class Index extends Component
{
    public ExerciseForm $form;

    public function save(CreateExercise $createExercise): void
    {
        $input = $this->form->validatedInput();

        $exerciseId = $createExercise->handle(
            userId: UserId::fromString((string) Auth::id()),
            name: $input->name,
            equipmentId: $input->equipmentId,
            primaryTarget: $input->primaryTarget,
            secondaryTarget: $input->secondaryTarget,
            recordingMethod: RecordingMethod::WeightAndRepetitions,
        );

        abort_if($exerciseId === null, 404);

        $this->form->reset();
        session()->flash('status', '種目を登録しました。');
    }

    public function render(
        ExerciseListQuery $exerciseList,
        EquipmentListQuery $equipmentList,
    ): View {
        $userId = UserId::fromString((string) Auth::id());

        return view('livewire.exercise.index', [
            'exerciseItems' => $exerciseList->forUser($userId),
            'equipmentItems' => $equipmentList->forUser($userId),
        ]);
    }
}
