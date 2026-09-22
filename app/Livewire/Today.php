<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Today | Tsumu')]
final class Today extends Component
{
    public function render(): View
    {
        return view('livewire.today');
    }
}
