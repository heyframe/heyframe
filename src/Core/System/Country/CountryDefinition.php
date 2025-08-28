<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use HeyFrame\Core\Defaults;
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
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TaxFreeConfigField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Aggregate\ChannelCountry\ChannelCountryDefinition;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateDefinition;
use HeyFrame\Core\System\Country\Aggregate\CountryTranslation\CountryTranslationDefinition;
use HeyFrame\Core\System\Currency\Aggregate\CurrencyCountryRounding\CurrencyCountryRoundingDefinition;
use HeyFrame\Core\System\Tax\Aggregate\TaxRule\TaxRuleDefinition;

#[Package('fundamentals@discovery')]
class CountryDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'country';

    final public const TYPE_CUSTOMER_TAX_FREE = 'customer-tax-free';

    final public const TYPE_COMPANY_TAX_FREE = 'company-tax-free';

    final public const DEFAULT_ADDRESS_FORMAT = [
        ['address/company', 'symbol/dash', 'address/department'],
        ['address/first_name', 'address/last_name'],
        ['address/street'],
        ['address/zipcode', 'address/city'],
        ['address/country'],
    ];

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

    public function getDefaults(): array
    {
        $defaultTax = [
            'enabled' => false,
            'currencyId' => Defaults::CURRENCY,
            'amount' => 0,
        ];

        return [
            'vatIdRequired' => false,
            'postalCodeRequired' => false,
            'checkPostalCodePattern' => false,
            'checkAdvancedPostalCodePattern' => false,
            'customerTax' => $defaultTax,
            'companyTax' => $defaultTax,
            'isEu' => false,
        ];
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
            (new BoolField('shipping_available', 'shippingAvailable'))->addFlags(new ApiAware()),
            (new StringField('iso3', 'iso3'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new BoolField('display_state_in_registration', 'displayStateInRegistration'))->addFlags(new ApiAware()),
            (new BoolField('force_state_in_registration', 'forceStateInRegistration'))->addFlags(new ApiAware()),
            (new BoolField('check_vat_id_pattern', 'checkVatIdPattern'))->addFlags(new ApiAware()),
            (new BoolField('vat_id_required', 'vatIdRequired'))->addFlags(new ApiAware()),
            (new StringField('vat_id_pattern', 'vatIdPattern'))->addFlags(new ApiAware()),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            (new TaxFreeConfigField('customer_tax', 'customerTax'))->addFlags(new ApiAware()),
            (new TaxFreeConfigField('company_tax', 'companyTax'))->addFlags(new ApiAware()),
            (new BoolField('postal_code_required', 'postalCodeRequired'))->addFlags(new ApiAware()),
            (new BoolField('check_postal_code_pattern', 'checkPostalCodePattern'))->addFlags(new ApiAware()),
            (new BoolField('check_advanced_postal_code_pattern', 'checkAdvancedPostalCodePattern'))->addFlags(new ApiAware()),
            (new StringField('advanced_postal_code_pattern', 'advancedPostalCodePattern'))->addFlags(new ApiAware()),
            (new TranslatedField('addressFormat'))->addFlags(new ApiAware()),
            (new StringField('default_postal_code_pattern', 'defaultPostalCodePattern', 1024))->addFlags(new ApiAware()),
            (new BoolField('is_eu', 'isEu'))->addFlags(new ApiAware(), new Required()),

            (new OneToManyAssociationField('states', CountryStateDefinition::class, 'country_id', 'id'))
                ->addFlags(new ApiAware(), new CascadeDelete()),

            (new TranslationsAssociationField(CountryTranslationDefinition::class, 'country_id'))
                ->addFlags(new ApiAware(), new Required()),

            (new OneToManyAssociationField('customerAddresses', CustomerAddressDefinition::class, 'country_id', 'id'))
                ->addFlags(new RestrictDelete()),

            (new OneToManyAssociationField('orderAddresses', OrderAddressDefinition::class, 'country_id', 'id'))
                ->addFlags(new RestrictDelete()),

            (new OneToManyAssociationField('channelDefaultAssignments', ChannelDefinition::class, 'country_id', 'id'))
                ->addFlags(new RestrictDelete()),

            new ManyToManyAssociationField('channels', ChannelDefinition::class, ChannelCountryDefinition::class, 'country_id', 'channel_id'),

            (new OneToManyAssociationField('taxRules', TaxRuleDefinition::class, 'country_id', 'id'))
                ->addFlags(new RestrictDelete()),

            (new OneToManyAssociationField('currencyCountryRoundings', CurrencyCountryRoundingDefinition::class, 'country_id'))
                ->addFlags(new CascadeDelete()),
        ]);
    }
}
