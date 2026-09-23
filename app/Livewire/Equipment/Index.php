<?php

namespace App\Livewire\Equipment;

use App\Service\Query\EquipmentListItem;
use App\Service\Query\EquipmentListQuery;
use Domain\User\UserId;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('機材 | Tsumu')]
final class Index extends Component
{
    /**
     * @var list<array{id: string, name: string, category: string, weightUnit: string, weightIncrement: string}>
     */
    public array $equipmentItems = [];

    public function mount(EquipmentListQuery $equipmentList): void
    {
        $this->equipmentItems = array_map(
            fn (EquipmentListItem $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $this->categoryLabel($item->category),
                'weightUnit' => $item->weightUnit,
                'weightIncrement' => $item->weightIncrement,
            ],
            $equipmentList->forUser(UserId::fromString((string) Auth::id())),
        );
    }

    public function render(): View
    {
        return view('livewire.equipment.index');
    }

    private function categoryLabel(string $category): string
    {
        return match ($category) {
            'machine' => 'マシン',
            'free_weight' => 'フリーウェイト',
            'cardio' => '有酸素',
            'other' => 'その他',
        };
    }
}
