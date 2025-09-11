<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\SeoUrl;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SeoUrlEntity>
 */
#[Package('inventory')]
class SeoUrlCollection extends EntityCollection
{
    public function filterByChannelId(string $id): SeoUrlCollection
    {
        return $this->filter(static fn (SeoUrlEntity $seoUrl) => $seoUrl->getChannelId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'seo_url_collection';
    }

    protected function getExpectedClass(): string
    {
        return SeoUrlEntity::class;
    }
}
