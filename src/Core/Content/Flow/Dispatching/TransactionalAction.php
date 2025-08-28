<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching;

use HeyFrame\Core\Framework\Log\Package;

/**
 * When a flow action implements this interface, it will be executed within a database transaction.
 */
#[Package('after-sales')]
interface TransactionalAction
{
}
