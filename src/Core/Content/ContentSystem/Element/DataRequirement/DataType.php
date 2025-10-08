<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\DataRequirement;

use HeyFrame\Core\Framework\Log\Package;

/**
 * Enum representing the type of data requirement for content elements.
 *
 * Determines how data should be loaded and hydrated:
 * - Static: No loading needed (translations, snippets, config)
 * - Entity: Single entity from DAL
 * - Collection: Collection of entities
 * - Service: External service call or complex operation
 *
 * @internal
 */
#[Package('discovery')]
enum DataType: string
{
    case Static = 'static';
    case Entity = 'entity';
    case Collection = 'collection';
    case Service = 'service';

    public function requiresLoading(): bool
    {
        return $this !== self::Static;
    }

    public function isEntityBased(): bool
    {
        return $this === self::Entity || $this === self::Collection;
    }
}
