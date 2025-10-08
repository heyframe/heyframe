<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\TypeRegistry;

use HeyFrame\Core\Framework\Log\Package;

/**
 * Value object representing a registered content element type.
 *
 * This immutable object holds the metadata for a content element type,
 * including its unique identifier, category, hydrator class, and
 * optional configuration schema for validation.
 *
 * Content element categories:
 * - container: Structure and layout elements (no data hydration)
 * - static: Fixed content with translations/config (Phase 1 hydration)
 * - entity: Product/category data (Phase 2 hydration)
 * - service: Real-time/external service data (Phase 3 hydration)
 *
 * @internal
 */
#[Package('discovery')]
final class ContentElementType
{
    /**
     * @param string $id Unique type identifier (e.g., 'product-box', 'text-block')
     * @param string $category Element category (container, static, entity, service)
     * @param string $hydratorClass Fully-qualified hydrator class name
     * @param array<string, mixed> $schema Optional JSON schema for element configuration validation
     */
    public function __construct(
        private readonly string $id,
        private readonly string $category,
        private readonly string $hydratorClass,
        private readonly array $schema = []
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getHydratorClass(): string
    {
        return $this->hydratorClass;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSchema(): array
    {
        return $this->schema;
    }
}
