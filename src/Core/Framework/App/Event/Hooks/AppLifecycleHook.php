<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Event\Hooks;

use HeyFrame\Core\Framework\DataAbstractionLayer\Facade\RepositoryFacadeHookFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\Facade\RepositoryWriterFacadeHookFactory;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\System\SystemConfig\Facade\SystemConfigFacadeHookFactory;

/**
 * @internal only rely on the concrete hook implementations
 */
#[Package('framework')]
abstract class AppLifecycleHook extends Hook
{
    public static function getServiceIds(): array
    {
        return [
            RepositoryFacadeHookFactory::class,
            SystemConfigFacadeHookFactory::class,
            RepositoryWriterFacadeHookFactory::class,
        ];
    }
}
