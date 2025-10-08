<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataLoader;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Generic entity loader for content elements.
 *
 * Handles data requirements with type='entity' by loading single DAL entities.
 * Configuration is provided in the requirement specification, not inferred from properties.
 *
 * Supports loading any entity type by ID from element properties.
 * Uses sales channel repositories when available, falls back to regular repositories.
 *
 * @internal
 */
#[Package('discovery')]
class EntityLoader implements ContentDataLoaderInterface
{
    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionRegistry
    ) {
    }

    public static function getRequirementType(): string
    {
        return 'entity';
    }

    public function load(
        ContentElement $element,
        array $requirement,
        ChannelContext $context
    ): mixed {
        // Validate requirement is array (should always be true due to interface, but defensive)
        if (!\is_array($requirement)) {
            throw ContentSystemException::invalidDataRequirement(get_debug_type($requirement));
        }

        // Get entity type from requirement specification
        $entityType = $requirement['entity'] ?? null;

        if ($entityType === null || !\is_string($entityType)) {
            return null;
        }

        // Get property name containing the entity ID
        $propertyName = $requirement['property'] ?? $entityType;

        // Get entity ID from element property
        $entityId = $element->getProperty($propertyName);

        if ($entityId === null) {
            return null;
        }

        // If already an entity (from parent context), return as-is
        if ($entityId instanceof Entity) {
            return $entityId;
        }

        // Must be a string ID to load
        if (!\is_string($entityId)) {
            return null;
        }

        // Get associations from requirement specification
        $associations = $requirement['associations'] ?? [];
        if (!\is_array($associations)) {
            $associations = [];
        }

        // Load the entity
        return $this->loadEntity($entityType, $entityId, $associations, $context);
    }

    /**
     * Load a single entity by ID.
     */
    private function loadEntity(
        string $entityType,
        string $entityId,
        array $associations,
        ChannelContext $context
    ): mixed {
        $criteria = new Criteria([$entityId]);

        // Add associations
        foreach ($associations as $association) {
            if (\is_string($association)) {
                $criteria->addAssociation($association);
            }
        }

        // Get repository
        $repository = $this->getRepository($entityType, $context);

        // Execute search
        $result = $repository->search($criteria, $context->getContext());

        return $result->first();
    }

    /**
     * Get repository for entity type.
     */
    private function getRepository(string $entityType, ChannelContext $context): mixed
    {
        return $this->definitionRegistry->getRepository($entityType);
    }
}
