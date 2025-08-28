<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Aggregate\PluginTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PluginTranslationEntity>
 */
#[Package('framework')]
class PluginTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'plugin_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return PluginTranslationEntity::class;
    }
}
