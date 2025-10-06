<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<Sitemap>
 */
#[Package('discovery')]
class SitemapCollection extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return Sitemap::class;
    }
}
