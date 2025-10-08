<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Consumer;

use HeyFrame\Core\Content\ContentSystem\DataContext\ContextType;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Consumer definition for single entity contexts.
 *
 * Declares that an element accepts a single entity from a parent provider.
 *
 * Used for: Elements that need one specific entity
 * Example: Product header component accepting the product entity
 *
 * @internal
 */
#[Package('discovery')]
readonly class SingleContextConsumer implements ContextConsumerDefinition
{
    public function __construct(
        public bool $required = false
    ) {
    }

    public function getType(): ContextType
    {
        return ContextType::Single;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function toArray(): array
    {
        return [
            'type' => 'single',
            'required' => $this->required,
        ];
    }
}
