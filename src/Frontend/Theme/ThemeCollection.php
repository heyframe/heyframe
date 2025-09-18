<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ThemeEntity>
 */
#[Package('framework')]
class ThemeCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ThemeEntity::class;
    }
}
