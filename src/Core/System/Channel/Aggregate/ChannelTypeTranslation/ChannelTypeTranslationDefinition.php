<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Aggregate\ChannelTypeTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Aggregate\ChannelType\ChannelTypeDefinition;

#[Package('discovery')]
class ChannelTypeTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'channel_type_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ChannelTypeTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ChannelTypeTranslationEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return ChannelTypeDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required()),
            new StringField('manufacturer', 'manufacturer'),
            new StringField('description', 'description'),
            (new LongTextField('description_long', 'descriptionLong'))->addFlags(new ApiAware(), new AllowHtml()),
            new CustomFields(),
        ]);
    }
}
