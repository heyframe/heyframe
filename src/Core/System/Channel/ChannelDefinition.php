<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroupRegistrationChannel\CustomerGroupRegistrationChannelDefinition;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerWishlist\CustomerWishlistDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Payment\PaymentMethodDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionChannel\PromotionChannelDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyIdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Aggregate\ChannelAnalytics\ChannelAnalyticsDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelCountry\ChannelCountryDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelCurrency\ChannelCurrencyDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelLanguage\ChannelLanguageDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelPaymentMethod\ChannelPaymentMethodDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelTranslation\ChannelTranslationDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelType\ChannelTypeDefinition;
use HeyFrame\Core\System\Country\CountryDefinition;
use HeyFrame\Core\System\Currency\CurrencyDefinition;
use HeyFrame\Core\System\Language\LanguageDefinition;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeChannel\NumberRangeChannelDefinition;
use HeyFrame\Core\System\SystemConfig\SystemConfigDefinition;

#[Package('discovery')]
class ChannelDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'channel';
    final public const CALCULATION_TYPE_VERTICAL = 'vertical';
    final public const CALCULATION_TYPE_HORIZONTAL = 'horizontal';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ChannelCollection::class;
    }

    public function getEntityClass(): string
    {
        return ChannelEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'taxCalculationType' => self::CALCULATION_TYPE_HORIZONTAL,
            'homeEnabled' => true,
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
            (new FkField('type_id', 'typeId', ChannelTypeDefinition::class))->addFlags(new Required()),
            (new FkField('language_id', 'languageId', LanguageDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('customer_group_id', 'customerGroupId', CustomerGroupDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('currency_id', 'currencyId', CurrencyDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('payment_method_id', 'paymentMethodId', PaymentMethodDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('country_id', 'countryId', CountryDefinition::class))->addFlags(new ApiAware(), new Required()),
            new FkField('analytics_id', 'analyticsId', ChannelAnalyticsDefinition::class),

            (new IntField('navigation_category_depth', 'navigationCategoryDepth', 1))->addFlags(new ApiAware()),
            (new FkField('hreflang_default_domain_id', 'hreflangDefaultDomainId', ChannelDomainDefinition::class))->addFlags(new ApiAware()),
            (new TranslatedField('name'))->addFlags(new ApiAware()),
            (new StringField('short_name', 'shortName'))->addFlags(new ApiAware()),
            (new StringField('tax_calculation_type', 'taxCalculationType'))->addFlags(new ApiAware()),
            (new StringField('access_key', 'accessKey'))->addFlags(new Required()),
            (new JsonField('configuration', 'configuration'))->addFlags(new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new BoolField('hreflang_active', 'hreflangActive'))->addFlags(new ApiAware()),
            (new BoolField('maintenance', 'maintenance'))->addFlags(new ApiAware()),
            new ListField('maintenance_ip_whitelist', 'maintenanceIpWhitelist'),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(ChannelTranslationDefinition::class, 'channel_id'))->addFlags(new Required()),
            new ManyToManyAssociationField('currencies', CurrencyDefinition::class, ChannelCurrencyDefinition::class, 'channel_id', 'currency_id'),
            new ManyToManyAssociationField('languages', LanguageDefinition::class, ChannelLanguageDefinition::class, 'channel_id', 'language_id'),
            new ManyToManyAssociationField('countries', CountryDefinition::class, ChannelCountryDefinition::class, 'channel_id', 'country_id'),
            new ManyToManyAssociationField('paymentMethods', PaymentMethodDefinition::class, ChannelPaymentMethodDefinition::class, 'channel_id', 'payment_method_id'),
            new ManyToManyIdField('payment_method_ids', 'paymentMethodIds', 'paymentMethods'),
            new ManyToOneAssociationField('type', 'type_id', ChannelTypeDefinition::class, 'id', false),
            (new ManyToOneAssociationField('language', 'language_id', LanguageDefinition::class, 'id', false))->addFlags(new ApiAware()),
            new ManyToOneAssociationField('customerGroup', 'customer_group_id', CustomerGroupDefinition::class, 'id', false),
            (new ManyToOneAssociationField('currency', 'currency_id', CurrencyDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('paymentMethod', 'payment_method_id', PaymentMethodDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('country', 'country_id', CountryDefinition::class, 'id', false))->addFlags(new ApiAware()),
            new OneToManyAssociationField('orders', OrderDefinition::class, 'channel_id', 'id'),

            new OneToManyAssociationField('customers', CustomerDefinition::class, 'channel_id', 'id'),

            new TranslatedField('homeSlotConfig'),
            new TranslatedField('homeEnabled'),
            new TranslatedField('homeName'),
            new TranslatedField('homeMetaTitle'),
            new TranslatedField('homeMetaDescription'),
            new TranslatedField('homeKeywords'),

            (new OneToManyAssociationField('domains', ChannelDomainDefinition::class, 'channel_id', 'id'))->addFlags(new ApiAware(), new CascadeDelete()),

            (new OneToManyAssociationField('systemConfigs', SystemConfigDefinition::class, 'channel_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('productVisibilities', ProductVisibilityDefinition::class, 'channel_id'))->addFlags(new CascadeDelete()),
            (new OneToOneAssociationField('hreflangDefaultDomain', 'hreflang_default_domain_id', 'id', ChannelDomainDefinition::class, false))->addFlags(new ApiAware()),
            (new OneToManyAssociationField('numberRangeChannels', NumberRangeChannelDefinition::class, 'channel_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('promotionChannels', PromotionChannelDefinition::class, 'channel_id', 'id'))->addFlags(new CascadeDelete()),
            (new OneToOneAssociationField('analytics', 'analytics_id', 'id', ChannelAnalyticsDefinition::class, false))->addFlags(new CascadeDelete()),
            new ManyToManyAssociationField('customerGroupsRegistrations', CustomerGroupDefinition::class, CustomerGroupRegistrationChannelDefinition::class, 'channel_id', 'customer_group_id', 'id', 'id'),
            new OneToManyAssociationField('boundCustomers', CustomerDefinition::class, 'bound_channel_id', 'id'),
            (new OneToManyAssociationField('wishlists', CustomerWishlistDefinition::class, 'channel_id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
