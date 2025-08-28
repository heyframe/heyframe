<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
#[IsFlowEventAware]
interface CustomerGroupAware
{
    public const CUSTOMER_GROUP_ID = 'customerGroupId';

    public const CUSTOMER_GROUP = 'customerGroup';

    public function getCustomerGroupId(): string;
}
