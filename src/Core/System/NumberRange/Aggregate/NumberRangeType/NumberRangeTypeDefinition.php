<?php declare(strict_types=1);

namespace HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeType;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeChannel\NumberRangeChannelDefinition;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeTypeTranslation\NumberRangeTypeTranslationDefinition;
use HeyFrame\Core\System\NumberRange\NumberRangeDefinition;

#[Package('framework')]
class NumberRangeTypeDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'number_range_type';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return NumberRangeTypeCollection::class;
    }

    public function getEntityClass(): string
    {
        return NumberRangeTypeEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('technical_name', 'technicalName'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('typeName'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new BoolField('global', 'global'))->addFlags(new Required()),
            new TranslatedField('customFields'),

            (new OneToManyAssociationField('numberRanges', NumberRangeDefinition::class, 'type_id'))->addFlags(new RestrictDelete()),
            (new OneToManyAssociationField('numberRangeChannels', NumberRangeChannelDefinition::class, 'number_range_type_id'))->addFlags(new CascadeDelete()),
            (new TranslationsAssociationField(NumberRangeTypeTranslationDefinition::class, 'number_range_type_id'))->addFlags(new Required()),
        ]);
    }
}
