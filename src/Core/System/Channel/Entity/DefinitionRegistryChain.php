<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Entity;

use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\DefinitionNotFoundException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Exception\ChannelRepositoryNotFoundException;

/**
 * @internal
 */
#[Package('framework')]
class DefinitionRegistryChain
{
    public function __construct(
        private readonly DefinitionInstanceRegistry $core,
        private readonly ChannelDefinitionInstanceRegistry $channel
    ) {
    }

    public function get(string $class): EntityDefinition
    {
        if ($this->channel->has($class)) {
            return $this->channel->get($class);
        }

        return $this->core->get($class);
    }

    /**
     * @return EntityRepository<covariant EntityCollection<covariant Entity>>|ChannelRepository<covariant EntityCollection<covariant Entity>>
     */
    public function getRepository(string $entity): EntityRepository|ChannelRepository
    {
        try {
            return $this->channel->getChannelRepository($entity);
        } catch (ChannelRepositoryNotFoundException) {
            return $this->core->getRepository($entity);
        }
    }

    public function getByEntityName(string $type): EntityDefinition
    {
        try {
            return $this->channel->getByEntityName($type);
        } catch (DefinitionNotFoundException) {
            return $this->core->getByEntityName($type);
        }
    }

    public function has(string $type): bool
    {
        return $this->channel->has($type) || $this->core->has($type);
    }
}
