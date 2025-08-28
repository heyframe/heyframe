<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Facade;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\Framework\Script\Execution\Script;

/**
 * @internal
 */
#[Package('checkout')]
class PriceFactoryFactory extends HookServiceFactory
{
    public function __construct(private readonly ScriptPriceStubs $stubs)
    {
    }

    public function factory(Hook $hook, Script $script): PriceFactory
    {
        return new PriceFactory($this->stubs);
    }

    public function getName(): string
    {
        return 'price';
    }
}
