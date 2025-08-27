<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script\Execution;

/**
 * @internal
 */
interface DeprecatedHook
{
    public static function getDeprecationNotice(): string;
}
