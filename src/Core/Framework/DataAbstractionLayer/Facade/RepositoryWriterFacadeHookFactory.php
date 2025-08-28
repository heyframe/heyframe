<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Facade;

use HeyFrame\Core\Framework\Api\Sync\SyncService;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\AppContextCreator;
use HeyFrame\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\Framework\Script\Execution\Script;

/**
 * @internal
 */
#[Package('framework')]
class RepositoryWriterFacadeHookFactory extends HookServiceFactory
{
    public function __construct(
        private readonly DefinitionInstanceRegistry $registry,
        private readonly AppContextCreator $appContextCreator,
        private readonly SyncService $syncService
    ) {
    }

    public function factory(Hook $hook, Script $script): RepositoryWriterFacade
    {
        return new RepositoryWriterFacade(
            $this->registry,
            $this->syncService,
            $this->appContextCreator->getAppContext($hook, $script)
        );
    }

    public function getName(): string
    {
        return 'writer';
    }
}
