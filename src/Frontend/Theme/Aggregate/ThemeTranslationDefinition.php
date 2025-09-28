<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Aggregate;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Theme\ThemeDefinition;

#[Package('framework')]
class ThemeTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'theme_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ThemeTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ThemeTranslationEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return ThemeDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('description', 'description'))->addFlags(new ApiAware()),
            (new JsonField('labels', 'labels'))->addFlags(new ApiAware()),
            (new JsonField('help_texts', 'helpTexts'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware()),
        ]);
    }
}
