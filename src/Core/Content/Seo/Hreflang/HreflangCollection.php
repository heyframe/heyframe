<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\Hreflang;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\StructCollection;

/**
 * @extends StructCollection<HreflangStruct>
 */
#[Package('inventory')]
class HreflangCollection extends StructCollection
{
    public function getApiAlias(): string
    {
        return 'seo_hreflang_collection';
    }

    protected function getExpectedClass(): string
    {
        return HreflangStruct::class;
    }
}
