<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Distribution;

use HeyFrame\Core\Content\ContentSystem\DataContext\DistributionStrategyInterface;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Keyed distribution strategy.
 *
 * Distributes collection items by specific keys to named consumers.
 * Consumers specify which data they want using a 'data_key' property,
 * and this strategy matches them with corresponding collection keys.
 *
 * Example Use Case:
 * Featured products showcase with structure:
 * - featured-products (provides "products" with keyed distribution)
 *   - product-hero (accepts "product", data_key: "featured") → receives products['featured']
 *   - product-grid (accepts "products", data_key: "related") → receives products['related']
 *
 * Edge Cases:
 * - Missing key in collection: Consumer receives null
 * - Consumer without data_key: Consumer receives null
 * - Duplicate keys: First matching consumer gets the data
 * - Non-associative array: Consumers receive null (requires associative keys)
 *
 * @internal
 */
#[Package('discovery')]
class KeyedDistributor implements DistributionStrategyInterface
{
    public function supports(string $distribution): bool
    {
        return $distribution === 'keyed';
    }

    /**
     * Distribute collection items by keys to consumers.
     *
     * @param mixed $data Collection with associative keys to distribute
     * @param array<int, array<string, mixed>> $consumers Consumer elements with 'data_key' property
     * @param array<string, mixed> $config Distribution configuration (unused for keyed)
     *
     * @return array<int, mixed> Array mapping consumer index to collection item by key
     */
    public function distribute(mixed $data, array $consumers, array $config): array
    {
        // Ensure data is an array
        if (!\is_array($data)) {
            return array_fill(0, \count($consumers), null);
        }

        $result = [];
        foreach ($consumers as $index => $consumer) {
            // Get the data key from consumer configuration
            $dataKey = $consumer['data_key'] ?? null;

            if ($dataKey === null) {
                // Consumer doesn't specify which key it wants
                $result[$index] = null;
                continue;
            }

            // Try to find data for this key
            $result[$index] = $data[$dataKey] ?? null;
        }

        return $result;
    }
}
