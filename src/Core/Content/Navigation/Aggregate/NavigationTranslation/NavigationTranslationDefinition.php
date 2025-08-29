<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Aggregate\NavigationTranslation;

use HeyFrame\Core\Content\Navigation\NavigationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BreadcrumbField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class NavigationTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'navigation_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return NavigationTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return NavigationTranslationCollection::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return NavigationDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new Required()),
            (new BreadcrumbField())->addFlags(new ApiAware(), new WriteProtected()),
            new JsonField('slot_config', 'slotConfig'),
            (new StringField('link_type', 'linkType'))->addFlags(new ApiAware()),
            (new IdField('internal_link', 'internalLink'))->addFlags(new ApiAware()),
            (new StringField('external_link', 'externalLink'))->addFlags(new ApiAware()),
            (new BoolField('link_new_tab', 'linkNewTab'))->addFlags(new ApiAware()),
            (new LongTextField('description', 'description'))->addFlags(new ApiAware(), new AllowHtml()),
            (new LongTextField('meta_title', 'metaTitle'))->addFlags(new ApiAware()),
            (new LongTextField('meta_description', 'metaDescription'))->addFlags(new ApiAware()),
            (new LongTextField('keywords', 'keywords'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware()),
        ]);
    }
}
