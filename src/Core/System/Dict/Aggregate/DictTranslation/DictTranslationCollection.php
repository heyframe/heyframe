<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict\Aggregate\DictTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<DictTranslationEntity>
 */
#[Package('framework')]
class DictTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dict_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return DictTranslationEntity::class;
    }
}
