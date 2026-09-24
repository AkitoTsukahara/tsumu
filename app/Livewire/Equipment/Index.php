<?php

namespace App\Livewire\Equipment;

use App\Livewire\Forms\EquipmentForm;
use App\Service\Command\CreateEquipment;
use App\Service\Query\Equipment\EquipmentListQuery;
use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\WeightUnit;
use Domain\User\UserId;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('機材 | Tsumu')]
final class Index extends Component
{
    public EquipmentForm $form;

    public function save(CreateEquipment $createEquipment): void
    {
        $input = $this->form->validatedInput();

        $createEquipment->handle(
            userId: UserId::fromString((string) Auth::id()),
            name: $input['name'],
            category: EquipmentCategory::from($input['category']),
            weightUnit: WeightUnit::from($input['weightUnit']),
            weightIncrement: $input['weightIncrement'],
        );

        $this->form->reset('name', 'weightIncrement');
        session()->flash('status', '機材を登録しました。');
    }

    public function render(EquipmentListQuery $equipmentList): View
    {
        return view('livewire.equipment.index', [
            'equipmentItems' => $equipmentList->forUser(
                UserId::fromString((string) Auth::id()),
            ),
        ]);
    }
}
