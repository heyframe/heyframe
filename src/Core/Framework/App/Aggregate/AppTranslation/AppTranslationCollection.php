<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Aggregate\AppTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 *
 * @extends EntityCollection<AppTranslationEntity>
 */
#[Package('framework')]
class AppTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AppTranslationEntity::class;
    }
}
