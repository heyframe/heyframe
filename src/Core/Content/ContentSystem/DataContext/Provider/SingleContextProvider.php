<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Provider;

use HeyFrame\Core\Content\ContentSystem\DataContext\ContextType;
use HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config\BroadcastDistributionConfig;
use HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config\DistributionConfig;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Provider definition for single entity contexts.
 *
 * Single context providers always use broadcast distribution - the single
 * entity is distributed to all child elements that accept it.
 *
 * Used for: Detail pages, specific entity displays
 * Example: Product detail page providing the product to header, gallery, info components
 *
 * @internal
 */
#[Package('discovery')]
readonly class SingleContextProvider implements ContextProviderDefinition
{
    public function getType(): ContextType
    {
        return ContextType::Single;
    }

    public function getDistribution(): DistributionConfig
    {
        // Single entities always use broadcast distribution
        return new BroadcastDistributionConfig();
    }

    public function toArray(): array
    {
        return [
            'type' => 'single',
            'distribution' => 'broadcast',
        ];
    }
}
