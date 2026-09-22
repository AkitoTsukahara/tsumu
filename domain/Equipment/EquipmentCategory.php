<?php

declare(strict_types=1);

namespace Domain\Equipment;

enum EquipmentCategory: string
{
    case Machine = 'machine';
    case FreeWeight = 'free_weight';
    case Cardio = 'cardio';
    case Other = 'other';
}
