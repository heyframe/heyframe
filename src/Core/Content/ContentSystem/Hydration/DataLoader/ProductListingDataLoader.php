<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataLoader;

use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Content\Product\Channel\Listing\ProductListingLoader;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * Loads product listings for content elements.
 *
 * Handles data requirements with type='product_listing' by loading product listings.
 * Wraps the existing ProductListingLoader service to provide product listings
 * with filtering, sorting, pagination, and aggregations.
 *
 * This loader demonstrates how to integrate existing HeyFrame services
 * into the content system hydration pipeline.
 *
 * @internal
 */
#[Package('discovery')]
class ProductListingDataLoader implements ContentDataLoaderInterface
{
    public function __construct(
        private readonly ProductListingLoader $productListingLoader
    ) {
    }

    public static function getRequirementType(): string
    {
        return 'product_listing';
    }

    public function load(
        ContentElement $element,
        array $requirement,
        ChannelContext $context
    ): mixed {
        // Build criteria from requirement configuration and element properties
        $criteria = $this->buildCriteria($element, $requirement);

        // Load listing using existing ProductListingLoader service
        return $this->productListingLoader->load($criteria, $context);
    }

    /**
     * Build Criteria from requirement config and element properties.
     *
     * Requirement can specify:
     * - limit: Number of products per page
     * - associations: Entity associations to load
     *
     * Element properties can override/extend:
     * - limit: Override limit
     * - page: Current page number for pagination
     * - associations: Additional associations
     *
     * @param array<string, mixed> $requirement
     */
    private function buildCriteria(ContentElement $element, array $requirement): Criteria
    {
        $criteria = new Criteria();

        // Get limit from requirement or element property
        $limit = $element->getProperty('limit') ?? $requirement['limit'] ?? null;
        if (\is_int($limit) && $limit > 0) {
            $criteria->setLimit($limit);
        }

        // Get page from element properties for pagination
        $page = $element->getProperty('page');
        if (\is_int($page) && $page > 0) {
            $offset = ($page - 1) * ($limit ?? 24);
            $criteria->setOffset($offset);
        }

        // Apply associations from requirement
        $requirementAssociations = $requirement['associations'] ?? [];
        if (\is_array($requirementAssociations)) {
            foreach ($requirementAssociations as $association) {
                if (\is_string($association)) {
                    $criteria->addAssociation($association);
                }
            }
        }

        // Apply additional associations from element properties
        $elementAssociations = $element->getProperty('associations');
        if (\is_array($elementAssociations)) {
            foreach ($elementAssociations as $association) {
                if (\is_string($association)) {
                    $criteria->addAssociation($association);
                }
            }
        }

        // Additional criteria configuration can be added here
        // (filters, sorting, etc.) based on requirement and element properties

        return $criteria;
    }
}
