<?php

namespace App\Livewire\Workout;

use App\Service\Query\Workout\InProgressWorkoutQuery;
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

    public function mount(InProgressWorkoutQuery $inProgressWorkoutQuery): void
    {
        $workout = $inProgressWorkoutQuery->forUser($this->authenticatedUserId());

        if ($workout === null) {
            $this->redirectRoute('today');

            return;
        }

        $this->startedAtLabel = $workout->startedAt->format('Y年n月j日 H:i');
    }

    public function render(): View
    {
        return view('livewire.workout.index');
    }

    private function authenticatedUserId(): UserId
    {
        return UserId::fromString((string) Auth::id());
    }
}
