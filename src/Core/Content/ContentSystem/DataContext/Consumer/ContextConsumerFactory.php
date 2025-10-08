<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext\Consumer;

use HeyFrame\Core\Content\ContentSystem\DataContext\ContextType;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Factory for creating ContextConsumerDefinition objects from array data.
 *
 * Handles deserialization from ContentLayout.structure arrays into
 * type-safe consumer definition objects.
 *
 * @internal
 */
#[Package('discovery')]
class ContextConsumerFactory
{
    /**
     * Create consumer definition from array configuration.
     *
     * @param array<string, mixed> $config Consumer configuration array
     */
    public static function fromArray(array $config): ContextConsumerDefinition
    {
        $type = ContextType::from($config['type'] ?? 'single');
        $required = $config['required'] ?? false;

        return match ($type) {
            ContextType::Single => new SingleContextConsumer($required),
            ContextType::Collection => new CollectionContextConsumer($required),
        };
    }
}
