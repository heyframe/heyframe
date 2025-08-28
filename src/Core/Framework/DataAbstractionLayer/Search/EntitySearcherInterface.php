<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
interface EntitySearcherInterface
{
    public function search(EntityDefinition $definition, Criteria $criteria, Context $context): IdSearchResult;
}
