<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict\Aggregate\DictItem;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<DictItemEntity>
 */
#[Package('framework')]
class DictItemCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dict_item_collection';
    }

    protected function getExpectedClass(): string
    {
        return DictItemEntity::class;
    }
}
