<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Distribution;

use HeyFrame\Core\Content\ContentSystem\DataContext\DistributionStrategyInterface;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Indexed distribution strategy.
 *
 * Distributes collection items by position to specific child elements.
 * The first child receives the first collection item, second child receives
 * the second item, and so on.
 *
 * Example Use Case:
 * Product comparison page with structure:
 * - comparison-grid (provides "products" with indexed distribution)
 *   - product-card (accepts "product") → receives products[0]
 *   - product-card (accepts "product") → receives products[1]
 *   - product-card (accepts "product") → receives products[2]
 *
 * Edge Cases:
 * - Fewer items than consumers: Later consumers receive null
 * - More items than consumers: Extra items are ignored
 * - Empty collection: All consumers receive null
 *
 * @internal
 */
#[Package('discovery')]
class IndexedDistributor implements DistributionStrategyInterface
{
    public function supports(string $distribution): bool
    {
        return $distribution === 'indexed';
    }

    /**
     * Distribute collection items by index position.
     *
     * @param mixed $data Collection to distribute (must be array or iterable)
     * @param array<int, array<string, mixed>> $consumers Consumer elements
     * @param array<string, mixed> $config Distribution configuration (unused for indexed)
     *
     * @return array<int, mixed> Array mapping consumer index to collection item
     */
    public function distribute(mixed $data, array $consumers, array $config): array
    {
        // Ensure data is an array
        if (!\is_array($data)) {
            // If not iterable, return empty array for all consumers
            return array_fill(0, \count($consumers), null);
        }

        // Reindex array to ensure sequential numeric keys
        $items = array_values($data);

        $result = [];
        foreach ($consumers as $index => $consumer) {
            // Assign item at same index, or null if not available
            $result[$index] = $items[$index] ?? null;
        }

        return $result;
    }
}
