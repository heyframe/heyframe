<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Aggregate\ActionButtonTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @extends EntityCollection<ActionButtonTranslationEntity>
 */
#[Package('framework')]
class ActionButtonTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ActionButtonTranslationEntity::class;
    }
}
