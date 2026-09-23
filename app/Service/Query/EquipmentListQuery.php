<?php

declare(strict_types=1);

namespace App\Service\Query;

use Domain\User\UserId;

interface EquipmentListQuery
{
    /** @return list<EquipmentListItem> */
    public function forUser(UserId $userId): array;
}
