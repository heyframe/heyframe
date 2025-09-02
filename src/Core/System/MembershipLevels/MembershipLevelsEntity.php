<?php declare(strict_types=1);

namespace HeyFrame\Core\System\MembershipLevels;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class MembershipLevelsEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;
}
