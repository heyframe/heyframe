<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script\Api;

use HeyFrame\Core\Framework\Script\AppContextCreator;
use HeyFrame\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\Framework\Script\Execution\Script;

/**
 * @internal
 */
class AclFacadeHookFactory extends HookServiceFactory
{
    /**
     * @internal
     */
    public function __construct(private readonly AppContextCreator $appContextCreator)
    {
    }

    public function factory(Hook $hook, Script $script): AclFacade
    {
        return new AclFacade(
            $this->appContextCreator->getAppContext($hook, $script)
        );
    }

    public function getName(): string
    {
        return 'acl';
    }
}
