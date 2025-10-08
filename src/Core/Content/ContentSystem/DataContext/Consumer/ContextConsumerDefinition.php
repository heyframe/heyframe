<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Consumer;

use HeyFrame\Core\Content\ContentSystem\DataContext\ContextType;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Defines the contract for context consumer definitions.
 *
 * Consumer definitions declare what data an element accepts from its parents
 * and whether that data is required for the element to function.
 *
 * Implementations must:
 * - Be readonly (immutable)
 * - Declare their context type (single/collection)
 * - Specify if the context is required
 * - Support serialization via toArray()
 *
 * @internal
 */
#[Package('discovery')]
interface ContextConsumerDefinition
{
    /**
     * Get the type of context being accepted (single entity or collection).
     */
    public function getType(): ContextType;

    /**
     * Check if this context is required for the element to function.
     *
     * If true, an exception should be thrown if the context is not available.
     */
    public function isRequired(): bool;

    /**
     * Serialize consumer definition to array (for ContentLayout storage).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
