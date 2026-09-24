<?php

namespace App\Livewire\Equipment;

use App\Livewire\Forms\EquipmentForm;
use App\Service\Command\CreateEquipment;
use App\Service\Command\UpdateEquipment;
use App\Service\Query\Equipment\Dto\EquipmentListItemDto;
use App\Service\Query\Equipment\EquipmentListQuery;
use Domain\Equipment\EquipmentId;
use Domain\Shared\Exceptions\InvalidUuidV7IdException;
use Domain\User\UserId;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('機材 | Tsumu')]
final class Index extends Component
{
    public EquipmentForm $form;

    #[Locked]
    public ?string $editingEquipmentId = null;

    public function save(CreateEquipment $createEquipment): void
    {
        $input = $this->form->validatedInput();

        $createEquipment->handle(
            userId: $this->authenticatedUserId(),
            name: $input->name,
            category: $input->category,
            weightUnit: $input->weightUnit,
            weightIncrement: $input->weightIncrement,
        );

        $this->form->reset('name', 'weightIncrement');
        session()->flash('status', '機材を登録しました。');
    }

    public function edit(EquipmentListQuery $equipmentList, string $equipmentId): void
    {
        $equipment = $equipmentList
            ->forUser($this->authenticatedUserId())
            ->find(fn (EquipmentListItemDto $equipment): bool => $equipment->id === $equipmentId);

        abort_if($equipment === null, 404);

        $this->editingEquipmentId = $equipment->id;
        $this->form->fillFrom($equipment);
    }

    public function cancelEditing(): void
    {
        $this->editingEquipmentId = null;
        $this->form->reset();
        $this->form->resetValidation();
    }

    public function update(UpdateEquipment $updateEquipment): void
    {
        $input = $this->form->validatedInput();

        $updated = $updateEquipment->handle(
            equipmentId: $this->validatedEditingEquipmentId(),
            userId: $this->authenticatedUserId(),
            name: $input->name,
            category: $input->category,
            weightUnit: $input->weightUnit,
            weightIncrement: $input->weightIncrement,
        );

        abort_unless($updated, 404);

        $this->cancelEditing();
        session()->flash('status', '機材を更新しました。');
    }

    public function render(EquipmentListQuery $equipmentList): View
    {
        return view('livewire.equipment.index', [
            'equipmentItems' => $equipmentList->forUser(
                $this->authenticatedUserId(),
            ),
        ]);
    }

    private function authenticatedUserId(): UserId
    {
        return UserId::fromString((string) Auth::id());
    }

    private function validatedEditingEquipmentId(): EquipmentId
    {
        try {
            return EquipmentId::fromString($this->editingEquipmentId ?? '');
        } catch (InvalidUuidV7IdException) {
            abort(404);
        }
    }
}
