<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script\Api;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ChannelContextAware;
use HeyFrame\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\Framework\Script\Execution\Script;
use HeyFrame\Storefront\Controller\ScriptController;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 */
#[Package('framework')]
class ScriptResponseFactoryFacadeHookFactory extends HookServiceFactory
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly ?ScriptController $scriptController
    ) {
    }

    public function factory(Hook $hook, Script $script): ScriptResponseFactoryFacade
    {
        $channelContext = null;
        if ($hook instanceof ChannelContextAware) {
            $channelContext = $hook->getChannelContext();
        }

        return new ScriptResponseFactoryFacade(
            $this->router,
            $this->scriptController,
            $channelContext
        );
    }

    public function getName(): string
    {
        return 'response';
    }
}
