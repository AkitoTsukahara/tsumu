<?php

declare(strict_types=1);

namespace Infra\Persistence\Eloquent\Models;

use Database\Factories\WorkoutSetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id',
    'workout_id',
    'exercise_id',
    'position',
    'weight',
    'repetitions',
    'completed_at',
])]
#[UseFactory(WorkoutSetFactory::class)]
final class WorkoutSet extends Model
{
    /** @use HasFactory<WorkoutSetFactory> */
    use HasFactory, HasUuids;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'weight' => 'decimal:2',
            'repetitions' => 'integer',
            'completed_at' => 'immutable_datetime',
        ];
    }
}
