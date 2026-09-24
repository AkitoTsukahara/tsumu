<?php

namespace App\Livewire\Exercise;

use App\Service\Query\Exercise\ExerciseListQuery;
use Domain\User\UserId;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('種目 | Tsumu')]
final class Index extends Component
{
    public function render(ExerciseListQuery $exerciseList): View
    {
        return view('livewire.exercise.index', [
            'exerciseItems' => $exerciseList->forUser(
                UserId::fromString((string) Auth::id()),
            ),
        ]);
    }
}
