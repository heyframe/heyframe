<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Aggregate\ChannelTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;

#[Package('discovery')]
class ChannelTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'channel_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ChannelTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ChannelTranslationEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'homeEnabled' => true,
        ];
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return ChannelDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        $fields = new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new Required()),
            new JsonField('home_slot_config', 'homeSlotConfig'),
            (new BoolField('home_enabled', 'homeEnabled'))->addFlags(new Required()),
            new StringField('home_name', 'homeName'),
            new StringField('home_meta_title', 'homeMetaTitle'),
            new StringField('home_meta_description', 'homeMetaDescription'),
            new StringField('home_keywords', 'homeKeywords'),
            (new CustomFields())->addFlags(new ApiAware()),
        ]);

        return $fields;
    }
}
