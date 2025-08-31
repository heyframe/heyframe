<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Aggregate\ChannelCountry\ChannelCountryDefinition;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateDefinition;
use HeyFrame\Core\System\Country\Aggregate\CountryTranslation\CountryTranslationDefinition;
use HeyFrame\Core\System\Currency\Aggregate\CurrencyCountryRounding\CurrencyCountryRoundingDefinition;

#[Package('fundamentals@discovery')]
class CountryDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'country';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return CountryCollection::class;
    }

    public function getEntityClass(): string
    {
        return CountryEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),

            (new TranslatedField('name'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new StringField('iso', 'iso'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new IntField('position', 'position'))->addFlags(new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new StringField('iso3', 'iso3'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),

            (new OneToManyAssociationField('states', CountryStateDefinition::class, 'country_id', 'id'))
                ->addFlags(new ApiAware(), new CascadeDelete()),

            (new TranslationsAssociationField(CountryTranslationDefinition::class, 'country_id'))
                ->addFlags(new ApiAware(), new Required()),

            (new OneToManyAssociationField('channelDefaultAssignments', ChannelDefinition::class, 'country_id', 'id'))
                ->addFlags(new RestrictDelete()),

            new ManyToManyAssociationField('channels', ChannelDefinition::class, ChannelCountryDefinition::class, 'country_id', 'channel_id'),

            (new OneToManyAssociationField('currencyCountryRoundings', CurrencyCountryRoundingDefinition::class, 'country_id'))
                ->addFlags(new CascadeDelete()),
        ]);
    }
}
