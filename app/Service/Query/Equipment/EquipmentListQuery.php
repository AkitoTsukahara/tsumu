<?php

declare(strict_types=1);

namespace App\Service\Query\Equipment;

use App\Service\Query\Equipment\Dto\EquipmentListItemCollection;
use Domain\User\UserId;

interface EquipmentListQuery
{
    public function forUser(UserId $userId): EquipmentListItemCollection;
}
