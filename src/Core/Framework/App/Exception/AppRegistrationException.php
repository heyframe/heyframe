<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Exception;

use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppRegistrationException extends AppException
{
}
