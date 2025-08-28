<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script\Execution;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
interface DeprecatedHook
{
    public static function getDeprecationNotice(): string;
}
