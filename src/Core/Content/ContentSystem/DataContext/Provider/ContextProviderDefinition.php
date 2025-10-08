<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Provider;

use HeyFrame\Core\Content\ContentSystem\DataContext\ContextType;
use HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config\DistributionConfig;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Defines the contract for context provider definitions.
 *
 * Provider definitions declare what data an element provides to its children
 * and how that data should be distributed.
 *
 * Implementations must:
 * - Be readonly (immutable)
 * - Declare their context type (single/collection)
 * - Provide distribution configuration
 * - Support serialization via toArray()
 *
 * @internal
 */
#[Package('discovery')]
interface ContextProviderDefinition
{
    /**
     * Get the type of context being provided (single entity or collection).
     */
    public function getType(): ContextType;

    /**
     * Get the distribution configuration for this provider.
     */
    public function getDistribution(): DistributionConfig;

    /**
     * Serialize provider definition to array (for ContentLayout storage).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
