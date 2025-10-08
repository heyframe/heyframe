<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Provider;

use HeyFrame\Core\Content\ContentSystem\DataContext\ContextType;
use HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config\DistributionConfig;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Provider definition for collection contexts.
 *
 * Collection context providers can use any distribution strategy
 * (indexed, keyed, sliced, iterator, or broadcast) to distribute
 * collection items to child elements.
 *
 * Used for: Listings, grids, multi-entity displays
 * Example: Product listing providing products array to child product cards
 *
 * @internal
 */
#[Package('discovery')]
readonly class CollectionContextProvider implements ContextProviderDefinition
{
    public function __construct(
        private DistributionConfig $distribution
    ) {
    }

    public function getType(): ContextType
    {
        return ContextType::Collection;
    }

    public function getDistribution(): DistributionConfig
    {
        return $this->distribution;
    }

    public function toArray(): array
    {
        return array_merge(
            ['type' => 'collection'],
            $this->distribution->toArray()
        );
    }
}
