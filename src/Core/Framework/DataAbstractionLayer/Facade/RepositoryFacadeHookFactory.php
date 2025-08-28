<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Facade;

use HeyFrame\Core\Framework\Api\Acl\AclCriteriaValidator;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\AppContextCreator;
use HeyFrame\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\Framework\Script\Execution\Script;

/**
 * @internal
 */
#[Package('framework')]
class RepositoryFacadeHookFactory extends HookServiceFactory
{
    /**
     * @internal
     */
    public function __construct(
        private readonly DefinitionInstanceRegistry $registry,
        private readonly AppContextCreator $appContextCreator,
        private readonly RequestCriteriaBuilder $criteriaBuilder,
        private readonly AclCriteriaValidator $criteriaValidator
    ) {
    }

    public function factory(Hook $hook, Script $script): RepositoryFacade
    {
        return new RepositoryFacade(
            $this->registry,
            $this->criteriaBuilder,
            $this->criteriaValidator,
            $this->appContextCreator->getAppContext($hook, $script)
        );
    }

    public function getName(): string
    {
        return 'repository';
    }
}
