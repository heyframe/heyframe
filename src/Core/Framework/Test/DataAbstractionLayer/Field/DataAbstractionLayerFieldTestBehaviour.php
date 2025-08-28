<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEventFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\MappingEntityClassesException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Read\EntityReaderInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntityAggregatorInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearcherInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\VersionManager;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInstanceRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait DataAbstractionLayerFieldTestBehaviour
{
    /**
     * @var list<class-string<EntityDefinition>>
     */
    private array $addedDefinitions = [];

    /**
     * @var list<class-string<EntityDefinition>>
     */
    private array $addedChannelDefinitions = [];

    /**
     * @var list<class-string<EntityExtension>>
     */
    private array $addedExtensions = [];

    /**
     * @var array<class-string<EntityExtension>, class-string<EntityDefinition>>
     */
    private array $extensionDefinitionMap = [];

    protected function tearDown(): void
    {
        $this->removeExtension(...$this->addedExtensions);
        $this->removeDefinitions(...$this->addedDefinitions);
        $this->removeChannelDefinitions(...$this->addedChannelDefinitions);

        $this->addedDefinitions = [];
        $this->addedChannelDefinitions = [];
        $this->addedExtensions = [];
        $this->extensionDefinitionMap = [];
    }

    abstract protected static function getContainer(): ContainerInterface;

    /**
     * @param class-string<EntityDefinition> ...$definitionClasses
     */
    protected function registerDefinition(string ...$definitionClasses): EntityDefinition
    {
        $ret = null;

        foreach ($definitionClasses as $definitionClass) {
            if (static::getContainer()->has($definitionClass)) {
                /** @var EntityDefinition $definition */
                $definition = static::getContainer()->get($definitionClass);
            } else {
                $this->addedDefinitions[] = $definitionClass;
                $definition = new $definitionClass();

                $repoId = $definition->getEntityName() . '.repository';
                if (!static::getContainer()->has($repoId)) {
                    $repository = new EntityRepository(
                        $definition,
                        static::getContainer()->get(EntityReaderInterface::class),
                        static::getContainer()->get(VersionManager::class),
                        static::getContainer()->get(EntitySearcherInterface::class),
                        static::getContainer()->get(EntityAggregatorInterface::class),
                        static::getContainer()->get('event_dispatcher'),
                        static::getContainer()->get(EntityLoadedEventFactory::class)
                    );

                    static::getContainer()->set($repoId, $repository);
                }
            }

            static::getContainer()->get(DefinitionInstanceRegistry::class)->register($definition);

            if ($ret === null) {
                $ret = $definition;
            }
        }

        if (!$ret) {
            throw new \InvalidArgumentException('Need at least one definition class to register.');
        }

        return $ret;
    }

    /**
     * @param class-string<EntityDefinition> $definitionClass
     */
    protected function registerChannelDefinition(string $definitionClass): EntityDefinition
    {
        $serviceId = $this->getChannelDefinitionServiceId($definitionClass);

        if (static::getContainer()->has($serviceId)) {
            /** @var EntityDefinition $definition */
            $definition = static::getContainer()->get($serviceId);

            static::getContainer()->get(ChannelDefinitionInstanceRegistry::class)->register($definition);

            return $definition;
        }

        $channelDefinition = new $definitionClass();
        $this->addedChannelDefinitions[] = $definitionClass;
        static::getContainer()->get(ChannelDefinitionInstanceRegistry::class)->register($channelDefinition);

        return $channelDefinition;
    }

    /**
     * @param class-string<EntityDefinition> $definitionClass
     * @param class-string<EntityExtension> ...$extensionsClasses
     */
    protected function registerDefinitionWithExtensions(string $definitionClass, string ...$extensionsClasses): EntityDefinition
    {
        $definition = $this->registerDefinition($definitionClass);
        $this->registerDefinitionExtensions($extensionsClasses, $definitionClass, $definition);

        return $definition;
    }

    /**
     * @param class-string<EntityDefinition> $definitionClass
     * @param class-string<EntityExtension> ...$extensionsClasses
     */
    protected function registerChannelDefinitionWithExtensions(string $definitionClass, string ...$extensionsClasses): EntityDefinition
    {
        $definition = static::getContainer()->get(ChannelDefinitionInstanceRegistry::class)->get($definitionClass);
        $this->registerDefinitionExtensions($extensionsClasses, $definitionClass, $definition);

        return $definition;
    }

    /**
     * @param class-string<EntityExtension> ...$extensionsClasses
     */
    private function removeExtension(string ...$extensionsClasses): void
    {
        foreach ($extensionsClasses as $extensionsClass) {
            $extension = new $extensionsClass();
            TestCase::assertArrayHasKey($extensionsClass, $this->extensionDefinitionMap, \sprintf('Trying to remove not registered extension "%s".', $extensionsClass));

            $definitionClass = $this->extensionDefinitionMap[$extensionsClass];
            if (static::getContainer()->has($definitionClass)) {
                /** @var EntityDefinition $definition */
                $definition = static::getContainer()->get($definitionClass);

                $definition->removeExtension($extension);

                $channelDefinitionId = $this->getChannelDefinitionServiceId($definitionClass);

                if (static::getContainer()->has($channelDefinitionId)) {
                    /** @var EntityDefinition $definition */
                    $definition = static::getContainer()->get($channelDefinitionId);

                    $definition->removeExtension($extension);
                }
            }
        }
    }

    /**
     * @param class-string<EntityDefinition> ...$definitionClasses
     */
    private function removeDefinitions(string ...$definitionClasses): void
    {
        foreach ($definitionClasses as $definitionClass) {
            $definition = new $definitionClass();

            $registry = static::getContainer()->get(DefinitionInstanceRegistry::class);
            \Closure::bind(function () use ($definition): void {
                unset(
                    $this->definitions[$definition->getEntityName()],
                    $this->repositoryMap[$definition->getEntityName()],
                );

                try {
                    unset($this->entityClassMapping[$definition->getEntityClass()]);
                } catch (MappingEntityClassesException) {
                }
            }, $registry, $registry)();
        }
    }

    /**
     * @param class-string<EntityDefinition> ...$definitionClasses
     */
    private function removeChannelDefinitions(string ...$definitionClasses): void
    {
        foreach ($definitionClasses as $definitionClass) {
            $definition = new $definitionClass();

            $registry = static::getContainer()->get(ChannelDefinitionInstanceRegistry::class);
            \Closure::bind(function () use ($definition): void {
                unset(
                    $this->definitions[$definition->getEntityName()],
                    $this->repositoryMap[$definition->getEntityName()],
                    $this->entityClassMapping[$definition->getEntityClass()],
                );
            }, $registry, $registry)();
        }
    }

    /**
     * @param class-string<EntityDefinition> $definitionClass
     */
    private function getChannelDefinitionServiceId(string $definitionClass): string
    {
        return 'channel_definition.' . $definitionClass;
    }

    /**
     * @internal
     *
     * @param array<class-string<EntityExtension>> $extensionsClasses
     * @param class-string<EntityDefinition> $definitionClass
     */
    private function registerDefinitionExtensions(array $extensionsClasses, string $definitionClass, EntityDefinition $definition): void
    {
        foreach ($extensionsClasses as $extensionsClass) {
            $this->addedExtensions[] = $extensionsClass;
            $this->extensionDefinitionMap[$extensionsClass] = $definitionClass;

            if (static::getContainer()->has($extensionsClass)) {
                /** @var EntityExtension $extension */
                $extension = static::getContainer()->get($extensionsClass);
            } else {
                $extension = new $extensionsClass();
                static::getContainer()->set($extensionsClass, $extension);
            }

            $definition->addExtension($extension);
        }
    }
}
