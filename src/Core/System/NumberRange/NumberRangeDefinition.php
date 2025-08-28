<?php declare(strict_types=1);

namespace HeyFrame\Core\System\NumberRange;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeChannel\NumberRangeChannelDefinition;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeState\NumberRangeStateDefinition;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeTranslation\NumberRangeTranslationDefinition;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeType\NumberRangeTypeDefinition;

#[Package('framework')]
class NumberRangeDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'number_range';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return NumberRangeCollection::class;
    }

    public function getEntityClass(): string
    {
        return NumberRangeEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            (new FkField('type_id', 'typeId', NumberRangeTypeDefinition::class))->addFlags(new Required()),
            (new BoolField('global', 'global'))->addFlags(new Required()),
            new TranslatedField('name'),
            new TranslatedField('description'),
            (new StringField('pattern', 'pattern'))->addFlags(new Required()),
            (new IntField('start', 'start'))->addFlags(new Required()),
            new TranslatedField('customFields'),

            new ManyToOneAssociationField('type', 'type_id', NumberRangeTypeDefinition::class),
            (new OneToManyAssociationField('numberRangeChannels', NumberRangeChannelDefinition::class, 'number_range_id'))->addFlags(new CascadeDelete()),
            (new OneToOneAssociationField('state', 'id', 'number_range_id', NumberRangeStateDefinition::class, false))->addFlags(new CascadeDelete()),
            (new TranslationsAssociationField(NumberRangeTranslationDefinition::class, 'number_range_id'))->addFlags(new Required()),
        ]);
    }
}
