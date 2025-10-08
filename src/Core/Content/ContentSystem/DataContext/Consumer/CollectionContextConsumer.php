<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Consumer;

use HeyFrame\Core\Content\ContentSystem\DataContext\ContextType;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Consumer definition for collection contexts.
 *
 * Declares that an element accepts a collection (array) of entities
 * from a parent provider.
 *
 * Used for: Elements that process multiple entities
 * Example: Product row component accepting slice of products array
 *
 * @internal
 */
#[Package('discovery')]
readonly class CollectionContextConsumer implements ContextConsumerDefinition
{
    public function __construct(
        public bool $required = false
    ) {
    }

    public function getType(): ContextType
    {
        return ContextType::Collection;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function toArray(): array
    {
        return [
            'type' => 'collection',
            'required' => $this->required,
        ];
    }
}
