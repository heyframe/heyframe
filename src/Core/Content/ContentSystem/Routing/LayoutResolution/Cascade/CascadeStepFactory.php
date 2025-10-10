<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Routing\LayoutResolution\Cascade;

use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('discovery')]
final readonly class CascadeStepFactory
{
    public function __construct(
        private DefinitionInstanceRegistry $definitionRegistry
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public function create(array $config): CascadeStepInterface
    {
        $entityType = $config['entity'] ?? null;

        if ($entityType === null) {
            return new DefaultLayoutStep();
        }

        if (isset($config['via'])) {
            return new AssociationStep(
                entityType: $entityType,
                associationName: $config['via'],
                sourceEntityType: $config['from'] ?? null,
                definitionRegistry: $this->definitionRegistry
            );
        }

        return new DirectEntityStep($entityType);
    }
}
