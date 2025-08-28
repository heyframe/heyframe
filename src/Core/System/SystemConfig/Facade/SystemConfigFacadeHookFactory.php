<?php declare(strict_types=1);

namespace HeyFrame\Core\System\SystemConfig\Facade;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ChannelContextAware;
use HeyFrame\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\Framework\Script\Execution\Script;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;

/**
 * @internal
 */
#[Package('framework')]
class SystemConfigFacadeHookFactory extends HookServiceFactory
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly Connection $connection
    ) {
    }

    public function getName(): string
    {
        return 'config';
    }

    public function factory(Hook $hook, Script $script): SystemConfigFacade
    {
        $channelId = null;

        if ($hook instanceof ChannelContextAware) {
            $channelId = $hook->getChannelContext()->getChannelId();
        }

        return new SystemConfigFacade($this->systemConfigService, $this->connection, $script->getScriptAppInformation(), $channelId);
    }
}
