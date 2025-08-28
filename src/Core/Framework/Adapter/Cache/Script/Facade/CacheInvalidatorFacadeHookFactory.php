<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache\Script\Facade;

use HeyFrame\Core\Framework\Adapter\Cache\CacheInvalidator;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\Framework\Script\Execution\Script;

/**
 * @internal
 */
#[Package('framework')]
class CacheInvalidatorFacadeHookFactory extends HookServiceFactory
{
    public function __construct(private readonly CacheInvalidator $cacheInvalidator)
    {
    }

    public function factory(Hook $hook, Script $script): CacheInvalidatorFacade
    {
        return new CacheInvalidatorFacade($this->cacheInvalidator);
    }

    public function getName(): string
    {
        return 'cache';
    }
}
