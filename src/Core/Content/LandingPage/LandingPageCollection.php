<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\LandingPage;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<LandingPageEntity>
 */
#[Package('discovery')]
class LandingPageCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return LandingPageEntity::class;
    }
}
