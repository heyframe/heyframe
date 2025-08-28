<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Aggregate\CmsBlock;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @extends EntityCollection<AppCmsBlockEntity>
 */
#[Package('discovery')]
class AppCmsBlockCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AppCmsBlockEntity::class;
    }
}
