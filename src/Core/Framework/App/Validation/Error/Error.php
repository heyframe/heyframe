<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Validation\Error;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
abstract class Error extends \Exception
{
    abstract public function getMessageKey(): string;
}
