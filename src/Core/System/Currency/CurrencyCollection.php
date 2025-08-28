<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Currency;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<CurrencyEntity>
 */
#[Package('fundamentals@framework')]
class CurrencyCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'currency_collection';
    }

    protected function getExpectedClass(): string
    {
        return CurrencyEntity::class;
    }
}
