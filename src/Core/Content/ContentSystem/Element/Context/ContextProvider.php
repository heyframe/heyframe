<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\Context;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\DataContext\ContextType;
use HeyFrame\Core\Content\ContentSystem\DataContext\Distribution\Config\DistributionConfig;
use HeyFrame\Core\Content\ContentSystem\DataContext\DistributionStrategy;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Value object representing a context provider definition.
 *
 * Defines what context an element provides to its children and how
 * that context should be distributed.
 *
 * Includes behavior for validation and distribution strategy access.
 *
 * @internal
 */
#[Package('discovery')]
readonly class ContextProvider
{
    public function __construct(
        public ContextType $type,
        public DistributionStrategy $strategy,
        public DistributionConfig $config
    ) {
    }

    /**
     * Check if this provider can distribute the given data.
     *
     * Validates that data matches the expected type (single vs collection).
     */
    public function canDistribute(mixed $data): bool
    {
        if ($data === null) {
            return false;
        }

        return match ($this->type) {
            ContextType::Single => !\is_array($data) || $data instanceof \stdClass,
            ContextType::Collection => \is_array($data) || $data instanceof \Countable,
        };
    }

    /**
     * Validate data matches expected type.
     *
     * @throws ContentSystemException if data type is invalid
     */
    public function validateData(mixed $data): void
    {
        if ($data === null) {
            return; // null is always valid
        }

        match ($this->type) {
            ContextType::Single => !\is_array($data) ?: throw ContentSystemException::invalidContextType(
                'Provider',
                'non-array (single entity)',
                get_debug_type($data)
            ),
            ContextType::Collection => (\is_array($data) || $data instanceof \Countable) ?: throw ContentSystemException::invalidContextType(
                'Provider',
                'array or Countable (collection)',
                get_debug_type($data)
            ),
        };
    }

    /**
     * Get distribution configuration.
     */
    public function getDistribution(): DistributionConfig
    {
        return $this->config;
    }

    /**
     * Get distribution strategy name.
     */
    public function getStrategyName(): string
    {
        return $this->strategy->value;
    }

    /**
     * Convert to array structure (serialization).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return \array_merge(
            [
                'type' => $this->type->value,
                'strategy' => $this->strategy->value,
            ],
            $this->config->toArray()
        );
    }
}
