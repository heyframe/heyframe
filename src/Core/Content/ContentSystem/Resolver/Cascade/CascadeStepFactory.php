<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver\Cascade;

use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Factory for creating cascade step instances from configuration arrays.
 *
 * Detects step type from config structure:
 * - No 'entity' key → DefaultLayoutStep
 * - Has 'via' key → AssociationStep
 * - Otherwise → DirectEntityStep
 *
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
     * Create step from configuration array.
     *
     * @param array<string, mixed> $config
     */
    public function create(array $config): CascadeStepInterface
    {
        $entityType = $config['entity'] ?? null;

        // Default layout (no entity type)
        if ($entityType === null) {
            return new DefaultLayoutStep();
        }

        // Association traversal
        if (isset($config['via'])) {
            return new AssociationStep(
                entityType: $entityType,
                associationName: $config['via'],
                sourceEntityType: $config['from'] ?? null,
                definitionRegistry: $this->definitionRegistry
            );
        }

        // Direct entity lookup
        return new DirectEntityStep($entityType);
    }
}
