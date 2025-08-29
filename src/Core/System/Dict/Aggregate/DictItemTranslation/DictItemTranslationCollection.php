<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict\Aggregate\DictItemTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<DictItemTranslationEntity>
 */
#[Package('framework')]
class DictItemTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dict_item_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return DictItemTranslationEntity::class;
    }
}
