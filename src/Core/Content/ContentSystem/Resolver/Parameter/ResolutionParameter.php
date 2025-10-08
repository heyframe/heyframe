<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver\Parameter;

use HeyFrame\Core\Framework\Log\Package;

/**
 * Value object representing a single parameter that requires entity resolution.
 *
 * Combines the parameter metadata with its resolution configuration:
 * - Parameter name (from route)
 * - Placeholder name (for ResolvedData)
 * - Resolution config (entity type, match field, constraints)
 * - Actual value to resolve
 *
 * @internal
 */
#[Package('discovery')]
readonly class ResolutionParameter
{
    public function __construct(
        public string $name,
        public string $placeholder,
        public EntityResolution $resolution,
        public mixed $value
    ) {
    }

    /**
     * Get entity type for this parameter.
     */
    public function getEntityType(): string
    {
        return $this->resolution->entityType;
    }

    /**
     * Get match field for this parameter.
     */
    public function getMatchField(): string
    {
        return $this->resolution->matchField;
    }

    /**
     * Check if this parameter has constraints.
     */
    public function hasConstraints(): bool
    {
        return $this->resolution->hasConstraints();
    }
}
