<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\TypeRegistry;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Element\Schema\ElementTypeSchema;
use HeyFrame\Core\Content\ContentSystem\Hydration\ContentElementHydratorInterface;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Central registry for content element types and their hydrators.
 *
 * This service manages the registration and discovery of content element types.
 * Hydrators are registered via tagged services and cached for performance.
 *
 * The registry serves as a factory, providing the appropriate hydrator
 * for each content element based on its type identifier and category.
 *
 * @internal
 */
#[Package('discovery')]
class ContentElementTypeRegistry
{
    /**
     * @var array<string, ContentElementType>
     */
    private array $types = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $schemas = [];

    /**
     * @var array<string, ContentElementHydratorInterface>
     */
    private array $hydratorCache = [];

    /**
     * @var array<string, ContentElementHydratorInterface>
     */
    private array $categoryHydrators = [];

    /**
     * @param iterable<ContentElementHydratorInterface> $hydrators Hydrators from tagged iterator
     */
    public function __construct(
        private readonly iterable $hydrators
    ) {
        $this->initializeCategoryHydrators();
    }

    /**
     * Register a content element type from schema.
     *
     * @param ElementTypeSchema $schema Element type schema
     */
    public function registerFromSchema(ElementTypeSchema $schema): void
    {
        $typeId = $schema->getName();

        // Store schema for data requirements
        $this->schemas[$typeId] = $schema->toArray();

        // Register type (category determines hydrator)
        $this->types[$typeId] = new ContentElementType(
            $typeId,
            $schema->getCategory(),
            '', // No custom hydrator - category-based routing only
            $schema->getPropertiesSchema()
        );
    }

    /**
     * Register a content element type (legacy method).
     *
     * @param string $id Unique type identifier
     * @param string $category Element category (static, service)
     * @param string $hydratorClass Fully-qualified hydrator class name
     * @param array<string, mixed> $schema Optional configuration schema
     */
    public function registerType(
        string $id,
        string $category,
        string $hydratorClass,
        array $schema = []
    ): void {
        $this->types[$id] = new ContentElementType($id, $category, $hydratorClass, $schema);
    }

    /**
     * Get the hydrator for a specific content element type.
     *
     * Uses category-based routing for all hydrators - no custom hydrator support.
     * Category alone determines which generic hydrator handles the element.
     *
     * @throws ContentSystemException If type is not registered or hydrator not found
     */
    public function getHydrator(string $typeId): ContentElementHydratorInterface
    {
        // Check cache first
        if (isset($this->hydratorCache[$typeId])) {
            return $this->hydratorCache[$typeId];
        }

        // Ensure type is registered
        if (!isset($this->types[$typeId])) {
            throw ContentSystemException::typeNotRegistered($typeId);
        }

        // Use category-based hydrator (generic)
        $category = $this->types[$typeId]->getCategory();

        if (!isset($this->categoryHydrators[$category])) {
            throw ContentSystemException::hydratorNotFound(
                $typeId,
                'Category hydrator for "' . $category . '"'
            );
        }

        $hydrator = $this->categoryHydrators[$category];
        $this->hydratorCache[$typeId] = $hydrator;

        return $hydrator;
    }

    /**
     * Get the full schema for a content element type.
     *
     * @return array<string, mixed>
     */
    public function getSchema(string $typeId): array
    {
        return $this->schemas[$typeId] ?? [];
    }

    /**
     * Get the category for a content element type.
     */
    public function getCategory(string $typeId): string
    {
        if (!isset($this->types[$typeId])) {
            // Default to 'service' for unknown types (backwards compatibility)
            return 'service';
        }

        return $this->types[$typeId]->getCategory();
    }

    /**
     * Check if a type is registered.
     */
    public function hasType(string $typeId): bool
    {
        return isset($this->types[$typeId]);
    }

    /**
     * Get all registered types.
     *
     * @return array<string, ContentElementType>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Validate element structure against its type schema.
     *
     * @param array<string, mixed> $element Element structure to validate
     *
     * @throws ContentSystemException If validation fails
     */
    public function validateElement(array $element): void
    {
        $typeId = $element['type'] ?? null;

        if ($typeId === null) {
            throw ContentSystemException::elementMissingType();
        }

        if (!isset($this->types[$typeId])) {
            // Type not registered - skip validation (allow dynamic types)
            return;
        }

        $type = $this->types[$typeId];
        $schema = $type->getSchema();

        if (empty($schema)) {
            // No schema defined - skip validation
            return;
        }

        // TODO: Implement JSON schema validation
        // For now, basic validation is sufficient
    }

    /**
     * Initialize category-based hydrators.
     *
     * Maps generic hydrators to their categories for schema-driven hydration.
     * Only two categories exist: static and service.
     */
    private function initializeCategoryHydrators(): void
    {
        foreach ($this->hydrators as $hydrator) {
            // Determine category by hydrator class name
            $className = $hydrator::class;

            if (str_contains($className, 'StaticElementHydrator')) {
                $this->categoryHydrators['static'] = $hydrator;
            } elseif (str_contains($className, 'ServiceElementHydrator')) {
                $this->categoryHydrators['service'] = $hydrator;
            }
        }
    }
}
