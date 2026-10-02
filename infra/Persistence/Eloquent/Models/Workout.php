<?php

declare(strict_types=1);

namespace Infra\Persistence\Eloquent\Models;

use Database\Factories\WorkoutFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id',
    'user_id',
    'started_at',
    'completed_at',
])]
#[UseFactory(WorkoutFactory::class)]
final class Workout extends Model
{
    /** @use HasFactory<WorkoutFactory> */
    use HasFactory, HasUuids;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
        ];
    }
}
