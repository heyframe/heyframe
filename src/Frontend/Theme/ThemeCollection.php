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
    public function getByTechnicalName(string $technicalName): ?ThemeEntity
    {
        return $this->filter(fn (ThemeEntity $theme) => $theme->getTechnicalName() === $technicalName)->first();
    }

    protected function getExpectedClass(): string
    {
        return ThemeEntity::class;
    }
}
