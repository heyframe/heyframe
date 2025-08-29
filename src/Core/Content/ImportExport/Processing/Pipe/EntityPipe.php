<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Processing\Pipe;

use HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Entity\AbstractEntitySerializer;
use HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\PrimaryKeyResolver;
use HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\SerializerRegistry;
use HeyFrame\Core\Content\ImportExport\Struct\Config;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
class EntityPipe extends AbstractPipe
{
    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly SerializerRegistry $serializerRegistry,
        private ?EntityDefinition $definition = null,
        private ?AbstractEntitySerializer $entitySerializer = null,
        private readonly ?PrimaryKeyResolver $primaryKeyResolver = null
    ) {
    }

    /**
     * @param array<mixed> $record
     */
    public function in(Config $config, iterable $record): iterable
    {
        $this->loadConfig($config);

        return $this->entitySerializer->serialize($config, $this->definition, $record);
    }

    public function out(Config $config, iterable $record): iterable
    {
        $this->loadConfig($config);

        if ($this->primaryKeyResolver) {
            $record = $this->primaryKeyResolver->resolvePrimaryKeyFromUpdatedBy($config, $this->definition, $record);
        }

        return $this->entitySerializer->deserialize($config, $this->definition, $record);
    }

    private function loadConfig(Config $config): void
    {
        $this->definition ??= $this->definitionInstanceRegistry->getByEntityName($config->get('sourceEntity') ?? '');

        $this->entitySerializer ??= $this->serializerRegistry->getEntity($this->definition->getEntityName());
    }
}
