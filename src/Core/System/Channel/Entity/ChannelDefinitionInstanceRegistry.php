<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Entity;

use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Exception\ChannelRepositoryNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Package('discovery')]
class ChannelDefinitionInstanceRegistry extends DefinitionInstanceRegistry
{
    /**
     * @internal
     *
     * @param array<string, string|class-string<EntityDefinition>> $definitionMap
     * @param array<string, string> $repositoryMap
     */
    public function __construct(
        private readonly string $prefix,
        ContainerInterface $container,
        array $definitionMap,
        array $repositoryMap
    ) {
        parent::__construct($container, $definitionMap, $repositoryMap);
    }

    /**
     * @throws ChannelRepositoryNotFoundException
     *
     * @return ChannelRepository<covariant EntityCollection<covariant Entity>>
     */
    public function getChannelRepository(string $entityName): ChannelRepository
    {
        $channelRepositoryClass = $this->getChannelRepositoryClassByEntityName($entityName);

        $channelRepository = $this->container->get($channelRepositoryClass);
        \assert($channelRepository instanceof ChannelRepository);

        return $channelRepository;
    }

    public function get(string $class): EntityDefinition
    {
        if (!str_starts_with($class, $this->prefix)) {
            $class = $this->prefix . $class;
        }

        return parent::get($class);
    }

    /**
     * @return array<ChannelDefinitionInterface>
     */
    public function getChannelDefinitions(): array
    {
        return array_filter($this->getDefinitions(), static fn ($definition): bool => $definition instanceof ChannelDefinitionInterface);
    }

    public function register(EntityDefinition $definition, ?string $serviceId = null): void
    {
        if (!$serviceId) {
            $serviceId = $this->prefix . $definition::class;
        }

        parent::register($definition, $serviceId);
    }

    /**
     * @throws ChannelRepositoryNotFoundException
     */
    private function getChannelRepositoryClassByEntityName(string $entityName): string
    {
        if (!isset($this->repositoryMap[$entityName])) {
            throw new ChannelRepositoryNotFoundException($entityName);
        }

        return $this->repositoryMap[$entityName];
    }
}
