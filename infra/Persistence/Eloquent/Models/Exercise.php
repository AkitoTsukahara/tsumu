<?php

declare(strict_types=1);

namespace Infra\Persistence\Eloquent\Models;

use Database\Factories\ExerciseFactory;
use Domain\Exercise\BodyPart;
use Domain\Exercise\RecordingMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'id',
    'user_id',
    'equipment_id',
    'name',
    'primary_target',
    'secondary_target',
    'recording_method',
])]
#[UseFactory(ExerciseFactory::class)]
final class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory, HasUuids;

    /** @return array<string, class-string> */
    protected function casts(): array
    {
        return [
            'primary_target' => BodyPart::class,
            'secondary_target' => BodyPart::class,
            'recording_method' => RecordingMethod::class,
        ];
    }
}
