<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\Context;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\DataContext\ContextType;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Value object representing a context consumer definition.
 *
 * Defines what context an element accepts from its parents and whether
 * that context is required for the element to function.
 *
 * Includes validation behavior to ensure received data matches expectations.
 *
 * @internal
 */
#[Package('discovery')]
readonly class ContextConsumer
{
    public function __construct(
        public ContextType $type,
        public bool $required
    ) {
    }

    /**
     * Validate data matches expected type and requirement.
     *
     * @throws ContentSystemException if validation fails
     */
    public function validateData(mixed $data): void
    {
        // Check if required but missing
        if ($this->required && $data === null) {
            throw ContentSystemException::requiredContextMissing('unknown');
        }

        // null is valid for optional contexts
        if ($data === null) {
            return;
        }

        // Type validation
        match ($this->type) {
            ContextType::Single => !\is_array($data) ?: throw ContentSystemException::invalidContextType(
                'Consumer',
                'non-array (single entity)',
                get_debug_type($data)
            ),
            ContextType::Collection => (\is_array($data) || $data instanceof \Countable) ?: throw ContentSystemException::invalidContextType(
                'Consumer',
                'array or Countable (collection)',
                get_debug_type($data)
            ),
        };
    }

    /**
     * Check if data satisfies this consumer (type match + requirement).
     */
    public function accepts(mixed $data): bool
    {
        try {
            $this->validateData($data);

            return true;
        } catch (ContentSystemException) {
            return false;
        }
    }

    /**
     * Convert to array structure (serialization).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'required' => $this->required,
        ];
    }
}
