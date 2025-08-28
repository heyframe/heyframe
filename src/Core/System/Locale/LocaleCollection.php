<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Locale;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<LocaleEntity>
 */
#[Package('discovery')]
class LocaleCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'locale_collection';
    }

    protected function getExpectedClass(): string
    {
        return LocaleEntity::class;
    }
}
