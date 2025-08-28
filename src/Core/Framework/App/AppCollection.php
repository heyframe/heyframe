<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @internal only for use by the app-system
 *
 * @extends EntityCollection<AppEntity>
 */
class AppCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AppEntity::class;
    }
}
