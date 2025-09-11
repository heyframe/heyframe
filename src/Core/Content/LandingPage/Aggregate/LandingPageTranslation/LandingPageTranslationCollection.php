<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\LandingPage\Aggregate\LandingPageTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<LandingPageTranslationEntity>
 */
#[Package('discovery')]
class LandingPageTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return LandingPageTranslationEntity::class;
    }
}
