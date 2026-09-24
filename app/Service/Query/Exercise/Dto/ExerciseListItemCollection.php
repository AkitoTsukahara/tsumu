<?php

declare(strict_types=1);

namespace App\Service\Query\Exercise\Dto;

use App\Service\Query\Dto\TypedList;

/** @extends TypedList<ExerciseListItemDto> */
final readonly class ExerciseListItemCollection extends TypedList
{
    protected static function itemType(): string
    {
        return ExerciseListItemDto::class;
    }
}
