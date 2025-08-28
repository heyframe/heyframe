<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @codeCoverageIgnore
 * Pseudo immutable collection
 *
 * @extends Collection<PluginRegionStruct>
 */
#[Package('checkout')]
final class PluginRegionCollection extends Collection
{
    public function getApiAlias(): string
    {
        return 'store_plugin_region_collection';
    }

    protected function getExpectedClass(): string
    {
        return PluginRegionStruct::class;
    }
}
