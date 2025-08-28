<?php declare(strict_types=1);

namespace HeyFrame\Core\Maintenance\System\Exception;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Maintenance\MaintenanceException;

/**
 * @internal
 */
#[Package('framework')]
class DatabaseSetupException extends MaintenanceException
{
}
