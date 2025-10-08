<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Distribution;

use HeyFrame\Core\Content\ContentSystem\DataContext\DistributionStrategyInterface;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Sliced distribution strategy.
 *
 * Divides a collection into chunks (slices) for pagination or grouping.
 * Each consumer receives a slice of the collection based on the configured
 * slice size.
 *
 * Example Use Case:
 * Product gallery with rows:
 * - product-gallery (provides "products" with sliced distribution, slice_size: 4)
 *   - product-row (accepts "product_slice") → receives products[0-3]
 *   - product-row (accepts "product_slice") → receives products[4-7]
 *   - product-row (accepts "product_slice") → receives products[8-11]
 *
 * Each row then uses iterator distribution internally to render individual products.
 *
 * Edge Cases:
 * - Collection smaller than slice_size: First consumer gets all items
 * - Collection not evenly divisible: Last slice contains remaining items
 * - No slice_size configured: Defaults to 1 item per slice
 * - Empty collection: All consumers receive empty array
 *
 * @internal
 */
#[Package('discovery')]
class SlicedDistributor implements DistributionStrategyInterface
{
    public function supports(string $distribution): bool
    {
        return $distribution === 'sliced';
    }

    /**
     * Distribute collection chunks to consumers.
     *
     * @param mixed $data Collection to slice and distribute
     * @param array<int, array<string, mixed>> $consumers Consumer elements
     * @param array<string, mixed> $config Distribution configuration with 'slice_size'
     *
     * @return array<int, mixed> Array mapping consumer index to collection slice
     */
    public function distribute(mixed $data, array $consumers, array $config): array
    {
        // Ensure data is an array
        if (!\is_array($data)) {
            return array_fill(0, \count($consumers), []);
        }

        // Get slice size from config, default to 1
        $sliceSize = (int) ($config['slice_size'] ?? 1);

        // Ensure slice size is at least 1
        if ($sliceSize < 1) {
            $sliceSize = 1;
        }

        // Reindex array to ensure sequential numeric keys
        $items = array_values($data);

        // Chunk the collection into slices
        $slices = array_chunk($items, $sliceSize);

        $result = [];
        foreach ($consumers as $index => $consumer) {
            // Assign slice at same index, or empty array if not available
            $result[$index] = $slices[$index] ?? [];
        }

        return $result;
    }
}
