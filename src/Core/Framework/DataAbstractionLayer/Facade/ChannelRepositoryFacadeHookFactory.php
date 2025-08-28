<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Facade;

use HeyFrame\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ChannelContextAware;
use HeyFrame\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\Framework\Script\Execution\Script;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInstanceRegistry;

/**
 * @internal
 */
#[Package('framework')]
class ChannelRepositoryFacadeHookFactory extends HookServiceFactory
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ChannelDefinitionInstanceRegistry $registry,
        private readonly RequestCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function factory(Hook $hook, Script $script): ChannelRepositoryFacade
    {
        if (!$hook instanceof ChannelContextAware) {
            throw DataAbstractionLayerException::hookInjectionException($hook, self::class, ChannelContextAware::class);
        }

        return new ChannelRepositoryFacade(
            $this->registry,
            $this->criteriaBuilder,
            $hook->getChannelContext()
        );
    }

    public function getName(): string
    {
        return 'store';
    }
}
