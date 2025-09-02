<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\MembershipPlans;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
class MembershipPlansEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;
}
