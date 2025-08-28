<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Aware;

use HeyFrame\Core\Framework\Event\IsFlowEventAware;

#[IsFlowEventAware]
interface CustomerRecoveryAware
{
    public const CUSTOMER_RECOVERY_ID = 'customerRecoveryId';

    public const CUSTOMER_RECOVERY = 'customerRecovery';

    public function getCustomerRecoveryId(): string;
}
