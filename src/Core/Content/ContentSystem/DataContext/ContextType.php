<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\DataContext;

use HeyFrame\Core\Framework\Log\Package;

/**
 * Defines the type of data context being provided or accepted.
 *
 * - Single: A single entity (e.g., one product, one category)
 * - Collection: Multiple entities (e.g., array of products)
 *
 * This enum provides type safety for context definitions and eliminates
 * string-based type checking.
 *
 * @internal
 */
#[Package('discovery')]
enum ContextType: string
{
    case Single = 'single';
    case Collection = 'collection';
}
