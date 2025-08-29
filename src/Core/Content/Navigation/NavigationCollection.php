<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<NavigationEntity>
 */
#[Package('discovery')]
class NavigationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'navigation_collection';
    }

    protected function getExpectedClass(): string
    {
        return NavigationEntity::class;
    }
}
