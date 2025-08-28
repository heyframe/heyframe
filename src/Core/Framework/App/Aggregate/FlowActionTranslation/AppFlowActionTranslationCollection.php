<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Aggregate\FlowActionTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<AppFlowActionTranslationEntity>
 */
#[Package('framework')]
class AppFlowActionTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AppFlowActionTranslationEntity::class;
    }
}
