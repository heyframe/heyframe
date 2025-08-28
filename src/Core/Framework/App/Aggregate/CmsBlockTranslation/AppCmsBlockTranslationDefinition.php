<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Aggregate\CmsBlockTranslation;

use HeyFrame\Core\Framework\App\Aggregate\CmsBlock\AppCmsBlockDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('discovery')]
class AppCmsBlockTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'app_cms_block_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return AppCmsBlockTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return AppCmsBlockTranslationCollection::class;
    }

    public function since(): ?string
    {
        return '6.4.2.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return AppCmsBlockDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('label', 'label'))->addFlags(new Required()),
        ]);
    }
}
