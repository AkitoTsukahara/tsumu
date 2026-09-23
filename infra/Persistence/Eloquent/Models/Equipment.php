<?php

declare(strict_types=1);

namespace Infra\Persistence\Eloquent\Models;

use Domain\Equipment\EquipmentCategory;
use Domain\Equipment\WeightUnit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'user_id', 'name', 'category', 'weight_unit', 'weight_increment'])]
final class Equipment extends Model
{
    use HasUuids;

    /** @return array<string, string|class-string> */
    protected function casts(): array
    {
        return [
            'category' => EquipmentCategory::class,
            'weight_unit' => WeightUnit::class,
            'weight_increment' => 'decimal:2',
        ];
    }
}
