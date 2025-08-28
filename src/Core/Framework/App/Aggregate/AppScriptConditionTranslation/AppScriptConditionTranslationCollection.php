<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Aggregate\AppScriptConditionTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<AppScriptConditionTranslationEntity>
 */
#[Package('framework')]
class AppScriptConditionTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'app_script_condition_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return AppScriptConditionTranslationEntity::class;
    }
}
