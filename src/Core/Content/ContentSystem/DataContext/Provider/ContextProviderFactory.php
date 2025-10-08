<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Provider;

use HeyFrame\Core\Content\ContentSystem\DataContext\ContextType;
use HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config\BroadcastDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config\IndexedDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config\IteratorDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config\KeyedDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config\SlicedDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\DataContext\DistributionStrategy;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Factory for creating ContextProviderDefinition objects from array data.
 *
 * Handles deserialization from ContentLayout.structure arrays into
 * type-safe provider definition objects.
 *
 * @internal
 */
#[Package('discovery')]
class ContextProviderFactory
{
    /**
     * Create provider definition from array configuration.
     *
     * @param array<string, mixed> $config Provider configuration array
     */
    public static function fromArray(array $config): ContextProviderDefinition
    {
        $type = ContextType::from($config['type'] ?? 'single');

        if ($type === ContextType::Single) {
            return new SingleContextProvider();
        }

        // Collection type - determine distribution strategy
        $strategyName = $config['distribution'] ?? 'broadcast';
        $strategy = DistributionStrategy::from($strategyName);

        $distributionConfig = match ($strategy) {
            DistributionStrategy::Indexed => IndexedDistributionConfig::fromArray($config),
            DistributionStrategy::Keyed => KeyedDistributionConfig::fromArray($config),
            DistributionStrategy::Sliced => SlicedDistributionConfig::fromArray($config),
            DistributionStrategy::Iterator => IteratorDistributionConfig::fromArray($config),
            DistributionStrategy::Broadcast => BroadcastDistributionConfig::fromArray($config),
        };

        return new CollectionContextProvider($distributionConfig);
    }
}
