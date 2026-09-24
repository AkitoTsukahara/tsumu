<?php

namespace App\Livewire\Exercise;

use App\Livewire\Forms\ExerciseForm;
use App\Service\Command\CreateExercise;
use App\Service\Command\UpdateExercise;
use App\Service\Query\Equipment\EquipmentListQuery;
use App\Service\Query\Exercise\Dto\ExerciseListItemDto;
use App\Service\Query\Exercise\ExerciseListQuery;
use Domain\Exercise\ExerciseId;
use Domain\Exercise\RecordingMethod;
use Domain\Shared\Exceptions\InvalidUuidV7IdException;
use Domain\User\UserId;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('種目 | Tsumu')]
final class Index extends Component
{
    public ExerciseForm $form;

    #[Locked]
    public ?string $editingExerciseId = null;

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

    public function edit(ExerciseListQuery $exerciseList, string $exerciseId): void
    {
        $exercise = $exerciseList
            ->forUser(UserId::fromString((string) Auth::id()))
            ->find(fn (ExerciseListItemDto $exercise): bool => $exercise->id === $exerciseId);

        abort_if($exercise === null, 404);

        $this->editingExerciseId = $exercise->id;
        $this->form->fillFrom($exercise);
    }

    public function cancelEditing(): void
    {
        $this->editingExerciseId = null;
        $this->form->reset();
        $this->form->resetValidation();
    }

    public function update(UpdateExercise $updateExercise): void
    {
        $input = $this->form->validatedInput();

        try {
            $exerciseId = ExerciseId::fromString($this->editingExerciseId ?? '');
        } catch (InvalidUuidV7IdException) {
            abort(404);
        }

        $updated = $updateExercise->handle(
            exerciseId: $exerciseId,
            userId: UserId::fromString((string) Auth::id()),
            name: $input->name,
            equipmentId: $input->equipmentId,
            primaryTarget: $input->primaryTarget,
            secondaryTarget: $input->secondaryTarget,
        );

        abort_unless($updated, 404);

        $this->cancelEditing();
        session()->flash('status', '種目を更新しました。');
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
