<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo;

use HeyFrame\Core\Content\Seo\Hreflang\HreflangCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
interface HreflangLoaderInterface
{
    public function load(HreflangLoaderParameter $parameter): HreflangCollection;
}
