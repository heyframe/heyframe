<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext;

use HeyFrame\Core\Framework\Log\Package;

/**
 * Defines the strategy for distributing context data to child elements.
 *
 * - Broadcast: Single entity distributed to all children
 * - Indexed: Collection items distributed by position
 * - Keyed: Collection items distributed by specific keys
 * - Sliced: Collection divided into chunks
 * - Iterator: Template repeated for each collection item
 *
 * This enum provides type safety for distribution strategy selection and
 * eliminates string-based strategy matching.
 *
 * @internal
 */
#[Package('discovery')]
enum DistributionStrategy: string
{
    case Broadcast = 'broadcast';
    case Indexed = 'indexed';
    case Keyed = 'keyed';
    case Sliced = 'sliced';
    case Iterator = 'iterator';
}
