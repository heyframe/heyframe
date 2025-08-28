<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Version;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<VersionEntity>
 */
#[Package('framework')]
class VersionCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dal_version_collection';
    }

    protected function getExpectedClass(): string
    {
        return VersionEntity::class;
    }
}
