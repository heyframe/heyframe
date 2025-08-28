<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\InAppPurchases\Response;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\AssignArrayTrait;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class InAppPurchasesResponse
{
    use AssignArrayTrait;

    /**
     * @var list<string>
     */
    public array $purchases = [];
}
