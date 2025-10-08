<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration;

use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Abstract base class for content element hydrators.
 *
 * Provides common functionality for all hydrators including:
 * - Helper methods for accessing element properties
 * - Association collection utilities
 *
 * Concrete hydrators should extend this class and implement:
 * - supports() method for determining if element is supported
 * - hydrate() method for actual data loading
 * - getRequiredAssociations() for preloading optimization (optional)
 *
 * @internal
 */
#[Package('discovery')]
abstract class AbstractContentElementHydrator implements ContentElementHydratorInterface
{
    /**
     * Get required associations for batch loading optimization.
     *
     * This method can be used by batch loading systems to collect
     * all required associations before loading entities.
     *
     * By default, returns empty array. Override in concrete hydrators
     * that need entity associations.
     *
     * @return array<string>
     */
    public function getRequiredAssociations(ContentElement $element): array
    {
        // By default, no associations required
        // Override in concrete hydrators that need entity associations
        return [];
    }

    /**
     * Get element property value with optional default.
     */
    protected function getProperty(ContentElement $element, string $key, mixed $default = null): mixed
    {
        return $element->getProperty($key) ?? $default;
    }

    /**
     * Get all element properties.
     *
     * @return array<string, mixed>
     */
    protected function getAllProperties(ContentElement $element): array
    {
        return $element->getProperties();
    }

    /**
     * Set property on the element.
     */
    protected function setProperty(ContentElement $element, string $key, mixed $value): void
    {
        $element->setProperty($key, $value);
    }

    /**
     * Check if element has a specific property.
     */
    protected function hasProperty(ContentElement $element, string $key): bool
    {
        return $element->hasProperty($key);
    }

    /**
     * Extract entity ID from element properties.
     *
     * @param string $propertyKey Property key containing the entity ID
     */
    protected function getEntityId(ContentElement $element, string $propertyKey = 'entity_id'): ?string
    {
        $value = $element->getProperty($propertyKey);

        if ($value === null) {
            return null;
        }

        // Return as string (should be resolved entity ID at this point)
        return (string) $value;
    }

    /**
     * Extract association list from element properties.
     *
     * @param string $propertyKey Property key containing associations
     *
     * @return array<string>
     */
    protected function getAssociations(ContentElement $element, string $propertyKey = 'associations'): array
    {
        $associations = $element->getProperty($propertyKey);

        if (!\is_array($associations)) {
            return [];
        }

        return $associations;
    }
}
