<?php declare(strict_types=1);

namespace HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeChannel;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeType\NumberRangeTypeDefinition;
use HeyFrame\Core\System\NumberRange\NumberRangeDefinition;

#[Package('framework')]
class NumberRangeChannelDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'number_range_channel';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return NumberRangeChannelCollection::class;
    }

    public function getEntityClass(): string
    {
        return NumberRangeChannelEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): ?string
    {
        return NumberRangeDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('number_range_id', 'numberRangeId', NumberRangeDefinition::class))->addFlags(new Required()),
            (new FkField('channel_id', 'channelId', ChannelDefinition::class))->addFlags(new Required()),
            new FkField('number_range_type_id', 'numberRangeTypeId', NumberRangeTypeDefinition::class),
            new ManyToOneAssociationField('numberRange', 'number_range_id', NumberRangeDefinition::class),
            new ManyToOneAssociationField('channel', 'channel_id', ChannelDefinition::class),
            new ManyToOneAssociationField('numberRangeType', 'number_range_type_id', NumberRangeTypeDefinition::class),
        ]);
    }
}
