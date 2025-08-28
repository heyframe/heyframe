<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
interface CriteriaPartInterface
{
    /**
     * @return list<string>
     */
    public function getFields(): array;
}
