<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config;

use HeyFrame\Core\Content\ContentSystem\DataContext\DistributionStrategy;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Defines the contract for distribution strategy configuration.
 *
 * Distribution configs are immutable value objects that encapsulate
 * the configuration parameters for a specific distribution strategy.
 * Each strategy (broadcast, indexed, keyed, sliced, iterator) has
 * its own concrete implementation with strategy-specific parameters.
 *
 * Implementations must:
 * - Be readonly (immutable)
 * - Provide serialization via toArray()
 * - Provide deserialization via fromArray()
 * - Declare their strategy via getStrategy()
 *
 * @internal
 */
#[Package('discovery')]
interface DistributionConfig
{
    /**
     * Get the distribution strategy this config belongs to.
     */
    public function getStrategy(): DistributionStrategy;

    /**
     * Serialize config to array (for ContentLayout storage).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Deserialize config from array (from ContentLayout storage).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self;
}
