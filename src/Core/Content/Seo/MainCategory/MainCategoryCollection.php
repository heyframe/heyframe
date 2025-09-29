<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\MainCategory;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MainCategoryEntity>
 */
#[Package('inventory')]
class MainCategoryCollection extends EntityCollection
{
    public function filterByChannelId(string $id): MainCategoryCollection
    {
        return $this->filter(static fn (MainCategoryEntity $mainCategory) => $mainCategory->getChannelId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'seo_main_category_collection';
    }

    protected function getExpectedClass(): string
    {
        return MainCategoryEntity::class;
    }
}
