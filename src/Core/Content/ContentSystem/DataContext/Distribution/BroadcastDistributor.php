<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Distribution;

use HeyFrame\Core\Content\ContentSystem\DataContext\DistributionStrategyInterface;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Broadcast distribution strategy.
 *
 * Distributes a single entity to all consumer elements. This is the simplest
 * distribution pattern and is used for detail pages where all child elements
 * need access to the same entity (e.g., product detail page where header,
 * gallery, description all need the same product).
 *
 * Example Use Case:
 * Product detail page with structure:
 * - product-detail (provides "product" with broadcast)
 *   - product-header (accepts "product")
 *   - product-gallery (accepts "product")
 *   - product-info (accepts "product")
 *   - product-description (accepts "product")
 *
 * All four child elements receive the same product entity.
 *
 * @internal
 */
#[Package('discovery')]
class BroadcastDistributor implements DistributionStrategyInterface
{
    public function supports(string $distribution): bool
    {
        return $distribution === 'broadcast';
    }

    /**
     * Distribute single entity to all consumers.
     *
     * @param mixed $data Single entity to broadcast
     * @param array<int, array<string, mixed>> $consumers Consumer elements
     * @param array<string, mixed> $config Distribution configuration (unused for broadcast)
     *
     * @return array<int, mixed> Array with same data for each consumer
     */
    public function distribute(mixed $data, array $consumers, array $config): array
    {
        // All consumers get the same data
        return array_fill(0, \count($consumers), $data);
    }
}
