<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class AndFilter extends MultiFilter
{
    public function __construct(array $queries = [])
    {
        parent::__construct(self::CONNECTION_AND, $queries);
    }
}
