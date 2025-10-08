<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext;

use HeyFrame\Core\Framework\Log\Package;

/**
 * Defines the contract for data distribution strategies.
 *
 * Distribution strategies determine how data from provider elements
 * is distributed to consumer elements in the content element tree.
 *
 * The Content System supports five distribution strategies:
 * - Broadcast: Single entity distributed to all children
 * - Indexed: Collection items distributed by position
 * - Iterator: Template repeated for each collection item
 * - Keyed: Collection items distributed by specific keys
 * - Sliced: Collection divided into chunks
 *
 * @internal
 */
#[Package('discovery')]
interface DistributionStrategyInterface
{
    /**
     * Check if this strategy supports the given distribution type.
     *
     * @param string $distribution Distribution type identifier (e.g., 'broadcast', 'iterator')
     */
    public function supports(string $distribution): bool;

    /**
     * Distribute data to consumer elements according to the strategy.
     *
     * @param mixed $data Data to distribute (single entity or collection)
     * @param array<int, array<string, mixed>> $consumers Consumer elements that accept this data
     * @param array<string, mixed> $config Distribution configuration from provider element
     *
     * @return array<int, mixed> Array mapping consumer index to distributed data
     */
    public function distribute(mixed $data, array $consumers, array $config): array;
}
