<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Aggregate\CmsBlockTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @extends EntityCollection<AppCmsBlockTranslationEntity>
 */
#[Package('discovery')]
class AppCmsBlockTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AppCmsBlockTranslationEntity::class;
    }
}
