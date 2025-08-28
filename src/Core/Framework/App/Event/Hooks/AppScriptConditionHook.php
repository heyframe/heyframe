<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Event\Hooks;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\TraceHook;

/**
 * @internal
 */
#[Package('framework')]
class AppScriptConditionHook extends TraceHook
{
    public static function getServiceIds(): array
    {
        return [];
    }

    public function getName(): string
    {
        return 'rule-conditions';
    }
}
