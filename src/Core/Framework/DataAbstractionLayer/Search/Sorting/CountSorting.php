<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting;

/**
 * @final
 */
class CountSorting extends FieldSorting
{
    protected string $type = 'count';
}
