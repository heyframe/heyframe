<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Distribution;

use HeyFrame\Core\Content\ContentSystem\DataContext\DistributionStrategyInterface;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Iterator distribution strategy.
 *
 * Processes a collection as a repeating template for each item. This strategy
 * is unique in that it signals the DataContextResolver to clone the template
 * element for each collection item, rather than distributing data to existing
 * consumers.
 *
 * Example Use Case:
 * Product listing with structure:
 * - product-listing (provides "products" with iterator distribution)
 *   - product-box (accepts "product", repeat: true) → template
 *
 * The template product-box is cloned once for each product in the collection.
 * Each clone receives one product entity.
 *
 * Unlike other strategies that distribute data to existing consumers, iterator
 * distribution modifies the element tree structure by creating new elements.
 * The DataContextResolver must handle this specially by:
 * 1. Detecting iterator distribution
 * 2. Cloning the template element N times (N = collection size)
 * 3. Distributing one collection item to each clone
 *
 * The distribute() method returns the collection items as-is, signaling to
 * the resolver that each item should get its own element instance.
 *
 * @internal
 */
#[Package('discovery')]
class IteratorDistributor implements DistributionStrategyInterface
{
    public function supports(string $distribution): bool
    {
        return $distribution === 'iterator';
    }

    /**
     * Return collection items for iterator distribution.
     *
     * This method returns the collection items as individual entries,
     * signaling to the DataContextResolver that each item should receive
     * its own cloned template element.
     *
     * Note: The actual element cloning is handled by DataContextResolver,
     * not by this distributor.
     *
     * @param mixed $data Collection to iterate over
     * @param array<int, array<string, mixed>> $consumers Consumer elements (typically single template)
     * @param array<string, mixed> $config Distribution configuration (unused for iterator)
     *
     * @return array<int, mixed> Array of collection items for distribution
     */
    public function distribute(mixed $data, array $consumers, array $config): array
    {
        // Ensure data is an array
        if (!\is_array($data)) {
            return [];
        }

        // Reindex array to ensure sequential numeric keys
        $items = array_values($data);

        // Return one item per entry - the resolver will create element clones
        return $items;
    }
}
