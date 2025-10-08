<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataLoader;

use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Defines the contract for content data loaders.
 *
 * Data loaders are responsible for loading specific types of data for content elements.
 * They are invoked by hydrators based on the requirement type declared in element schemas.
 *
 * Loaders advertise what requirement type they handle via static getRequirementType() method.
 * This creates a clean separation: requirements specify WHAT data is needed (declarative),
 * loaders provide HOW to load it (implementation).
 *
 * Common requirement types:
 * - 'entity': Load single DAL entities (products, categories, etc.)
 * - 'product_listing': Load product listings with criteria
 * - 'translation': Load translated content
 * - Custom types can be added by plugins
 *
 * Data loaders are registered as tagged services in ServiceLocator, keyed by requirement type.
 *
 * @internal
 */
#[Package('discovery')]
interface ContentDataLoaderInterface
{
    /**
     * Declare which requirement type this loader handles.
     *
     * This static method is used to build the ServiceLocator key mapping during DI compilation.
     * The returned value MUST match the 'type' field in data requirements that this loader handles.
     *
     * Example: EntityLoader returns 'entity' to handle requirements with type='entity'
     *
     * @return string The requirement type identifier (e.g., 'entity', 'product_listing', 'translation')
     */
    public static function getRequirementType(): string;

    /**
     * Load data based on requirement specification.
     *
     * The loader should:
     * 1. Read configuration from the $requirement array (entity name, associations, criteria, etc.)
     * 2. Extract necessary data from $element properties (IDs, filters, etc.)
     * 3. Load data using appropriate services/repositories
     * 4. Return loaded data for storage in element properties
     *
     * The returned data is typically:
     * - Single entity for entity loaders
     * - EntityCollection for collection loaders
     * - EntitySearchResult for listing/search loaders
     * - Translated strings for translation loaders
     * - Custom data structures for service loaders
     *
     * @param ContentElement $element Element to load data for
     * @param array<string, mixed> $requirement Full requirement specification from element schema
     * @param ChannelContext $context Sales channel context for data loading
     *
     * @return mixed Loaded data (entity, collection, search result, string, etc.)
     */
    public function load(
        ContentElement $element,
        array $requirement,
        ChannelContext $context
    ): mixed;
}
