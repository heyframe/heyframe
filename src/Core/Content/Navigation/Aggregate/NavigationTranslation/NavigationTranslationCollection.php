<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Aggregate\NavigationTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<NavigationTranslationEntity>
 */
#[Package('discovery')]
class NavigationTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'navigation_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return NavigationTranslationEntity::class;
    }
}
