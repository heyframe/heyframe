<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PluginEntity>
 */
#[Package('framework')]
class PluginCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'plugin_collection';
    }

    protected function getExpectedClass(): string
    {
        return PluginEntity::class;
    }
}
