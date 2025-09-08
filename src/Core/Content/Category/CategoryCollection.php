<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<CategoryEntity>
 */
#[Package('discovery')]
class CategoryCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'category_collection';
    }

    protected function getExpectedClass(): string
    {
        return CategoryEntity::class;
    }
}
