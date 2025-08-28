<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class StoreLicenseSubscriptionStruct extends Struct
{
    protected \DateTimeInterface $expirationDate;

    public function getApiAlias(): string
    {
        return 'store_license_subscription';
    }
}
