<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\Distribution\Config;

use HeyFrame\Core\Content\ContentSystem\Hydration\DataContext\DistributionStrategy;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Configuration for indexed distribution strategy.
 *
 * Indexed distributes collection items by position - first child receives
 * first item, second child receives second item, etc.
 *
 * Used for: Fixed-position layouts
 * Example: Product comparison grid with 3 product cards at fixed positions
 *
 * @internal
 */
#[Package('discovery')]
readonly class IndexedDistributionConfig implements DistributionConfig
{
    public function getStrategy(): DistributionStrategy
    {
        return DistributionStrategy::Indexed;
    }

    public function toArray(): array
    {
        return ['distribution' => 'indexed'];
    }

    public static function fromArray(array $data): self
    {
        return new self();
    }
}
