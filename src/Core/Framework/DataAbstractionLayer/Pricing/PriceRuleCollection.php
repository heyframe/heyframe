<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Pricing;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PriceRuleEntity>
 */
#[Package('framework')]
class PriceRuleCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dal_price_rule_collection';
    }

    protected function getExpectedClass(): string
    {
        return PriceRuleEntity::class;
    }
}
