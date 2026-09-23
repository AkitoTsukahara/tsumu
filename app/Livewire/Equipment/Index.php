<?php

namespace App\Livewire\Equipment;

use App\Service\Query\Equipment\EquipmentListQuery;
use Domain\User\UserId;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('機材 | Tsumu')]
final class Index extends Component
{
    public function render(EquipmentListQuery $equipmentList): View
    {
        return view('livewire.equipment.index', [
            'equipmentItems' => $equipmentList->forUser(
                UserId::fromString((string) Auth::id()),
            ),
        ]);
    }
}
