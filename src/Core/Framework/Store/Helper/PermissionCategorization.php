<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Helper;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressDefinition;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroupRegistrationChannel\CustomerGroupRegistrationChannelDefinition;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroupTranslation\CustomerGroupTranslationDefinition;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerRecovery\CustomerRecoveryDefinition;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerTag\CustomerTagDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Document\Aggregate\DocumentBaseConfig\DocumentBaseConfigDefinition;
use HeyFrame\Core\Checkout\Document\Aggregate\DocumentBaseConfigChannel\DocumentBaseConfigChannelDefinition;
use HeyFrame\Core\Checkout\Document\Aggregate\DocumentType\DocumentTypeDefinition;
use HeyFrame\Core\Checkout\Document\Aggregate\DocumentTypeTranslation\DocumentTypeTranslationDefinition;
use HeyFrame\Core\Checkout\Document\DocumentDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderDeliveryPosition\OrderDeliveryPositionDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTag\OrderTagDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Payment\Aggregate\PaymentMethodTranslation\PaymentMethodTranslationDefinition;
use HeyFrame\Core\Checkout\Payment\PaymentMethodDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionCartRule\PromotionCartRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionChannel\PromotionChannelDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscountPrice\PromotionDiscountPriceDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscountRule\PromotionDiscountRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionIndividualCode\PromotionIndividualCodeDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionOrderRule\PromotionOrderRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionPersonaCustomer\PromotionPersonaCustomerDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionPersonaRule\PromotionPersonaRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionSetGroup\PromotionSetGroupDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionSetGroupRule\PromotionSetGroupRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionTranslation\PromotionTranslationDefinition;
use HeyFrame\Core\Checkout\Promotion\PromotionDefinition;
use HeyFrame\Core\Content\Category\Aggregate\CategoryTag\CategoryTagDefinition;
use HeyFrame\Core\Content\Category\Aggregate\CategoryTranslation\CategoryTranslationDefinition;
use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockDefinition;
use HeyFrame\Core\Content\Cms\Aggregate\CmsPageTranslation\CmsPageTranslationDefinition;
use HeyFrame\Core\Content\Cms\Aggregate\CmsSection\CmsSectionDefinition;
use HeyFrame\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotDefinition;
use HeyFrame\Core\Content\Cms\Aggregate\CmsSlotTranslation\CmsSlotTranslationDefinition;
use HeyFrame\Core\Content\Cms\CmsPageDefinition;
use HeyFrame\Core\Content\ImportExport\Aggregate\ImportExportFile\ImportExportFileDefinition;
use HeyFrame\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogDefinition;
use HeyFrame\Core\Content\ImportExport\ImportExportProfileDefinition;
use HeyFrame\Core\Content\ImportExport\ImportExportProfileTranslationDefinition;
use HeyFrame\Core\Content\MailTemplate\Aggregate\MailHeaderFooter\MailHeaderFooterDefinition;
use HeyFrame\Core\Content\MailTemplate\Aggregate\MailHeaderFooterTranslation\MailHeaderFooterTranslationDefinition;
use HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateMedia\MailTemplateMediaDefinition;
use HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation\MailTemplateTranslationDefinition;
use HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeDefinition;
use HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateTypeTranslation\MailTemplateTypeTranslationDefinition;
use HeyFrame\Core\Content\MailTemplate\MailTemplateDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaDefaultFolder\MediaDefaultFolderDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaFolder\MediaFolderDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaFolderConfiguration\MediaFolderConfigurationDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaFolderConfigurationMediaThumbnailSize\MediaFolderConfigurationMediaThumbnailSizeDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaTag\MediaTagDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaThumbnailSize\MediaThumbnailSizeDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaTranslation\MediaTranslationDefinition;
use HeyFrame\Core\Content\Media\MediaDefinition;
use HeyFrame\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientDefinition;
use HeyFrame\Core\Content\Newsletter\Aggregate\NewsletterRecipientTag\NewsletterRecipientTagDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductCategory\ProductCategoryDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductCategoryTree\ProductCategoryTreeDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductCrossSellingAssignedProducts\ProductCrossSellingAssignedProductsDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductCrossSellingTranslation\ProductCrossSellingTranslationDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductCustomFieldSet\ProductCustomFieldSetDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductFeatureSet\ProductFeatureSetDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductFeatureSetTranslation\ProductFeatureSetTranslationDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductKeywordDictionary\ProductKeywordDictionaryDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductManufacturerTranslation\ProductManufacturerTranslationDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductMedia\ProductMediaDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductOption\ProductOptionDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductPrice\ProductPriceDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductProperty\ProductPropertyDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductSearchKeyword\ProductSearchKeywordDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductTag\ProductTagDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductTranslation\ProductTranslationDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Product\Channel\Sorting\ProductSortingDefinition;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\ProductExport\ProductExportDefinition;
use HeyFrame\Core\Content\ProductStream\Aggregate\ProductStreamFilter\ProductStreamFilterDefinition;
use HeyFrame\Core\Content\ProductStream\Aggregate\ProductStreamTranslation\ProductStreamTranslationDefinition;
use HeyFrame\Core\Content\ProductStream\ProductStreamDefinition;
use HeyFrame\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionDefinition;
use HeyFrame\Core\Content\Rule\RuleDefinition;
use HeyFrame\Core\Content\Seo\SeoUrl\SeoUrlDefinition;
use HeyFrame\Core\Content\Seo\SeoUrlTemplate\SeoUrlTemplateDefinition;
use HeyFrame\Core\Framework\App\Aggregate\ActionButton\ActionButtonDefinition;
use HeyFrame\Core\Framework\App\Aggregate\ActionButtonTranslation\ActionButtonTranslationDefinition;
use HeyFrame\Core\Framework\App\Aggregate\AppTranslation\AppTranslationDefinition;
use HeyFrame\Core\Framework\App\AppDefinition;
use HeyFrame\Core\Framework\App\Template\TemplateDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Version\VersionDefinition;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Aggregate\ChannelAnalytics\ChannelAnalyticsDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelCountry\ChannelCountryDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelCurrency\ChannelCurrencyDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelLanguage\ChannelLanguageDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelPaymentMethod\ChannelPaymentMethodDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelShippingMethod\ChannelShippingMethodDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelTranslation\ChannelTranslationDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelType\ChannelTypeDefinition;
use HeyFrame\Core\System\Channel\Aggregate\ChannelTypeTranslation\ChannelTypeTranslationDefinition;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateDefinition;
use HeyFrame\Core\System\Country\CountryDefinition;
use HeyFrame\Core\System\Currency\CurrencyDefinition;
use HeyFrame\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetDefinition;
use HeyFrame\Core\System\CustomField\Aggregate\CustomFieldSetRelation\CustomFieldSetRelationDefinition;
use HeyFrame\Core\System\CustomField\CustomFieldDefinition;
use HeyFrame\Core\System\DeliveryTime\DeliveryTimeDefinition;
use HeyFrame\Core\System\Integration\IntegrationDefinition;
use HeyFrame\Core\System\Language\LanguageDefinition;
use HeyFrame\Core\System\Locale\Aggregate\LocaleTranslation\LocaleTranslationDefinition;
use HeyFrame\Core\System\Locale\LocaleDefinition;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeChannel\NumberRangeChannelDefinition;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeState\NumberRangeStateDefinition;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeType\NumberRangeTypeDefinition;
use HeyFrame\Core\System\NumberRange\NumberRangeDefinition;
use HeyFrame\Core\System\Salutation\Aggregate\SalutationTranslation\SalutationTranslationDefinition;
use HeyFrame\Core\System\Salutation\SalutationDefinition;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineHistory\StateMachineHistoryDefinition;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateDefinition;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateTranslationDefinition;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionDefinition;
use HeyFrame\Core\System\StateMachine\StateMachineDefinition;
use HeyFrame\Core\System\StateMachine\StateMachineTranslationDefinition;
use HeyFrame\Core\System\SystemConfig\SystemConfigDefinition;
use HeyFrame\Core\System\Tag\TagDefinition;
use HeyFrame\Core\System\Tax\Aggregate\TaxRule\TaxRuleDefinition;
use HeyFrame\Core\System\Tax\Aggregate\TaxRuleType\TaxRuleTypeDefinition;
use HeyFrame\Core\System\Tax\TaxDefinition;
use HeyFrame\Core\System\Unit\UnitDefinition;
use HeyFrame\Core\System\User\Aggregate\UserAccessKey\UserAccessKeyDefinition;
use HeyFrame\Core\System\User\Aggregate\UserRecovery\UserRecoveryDefinition;
use HeyFrame\Core\System\User\UserDefinition;

/**
 * @internal
 */
#[Package('checkout')]
class PermissionCategorization
{
    private const CATEGORY_APP = 'app';
    private const CATEGORY_ADMIN_USER = 'admin_user';
    private const CATEGORY_CATEGORY = 'category';
    private const CATEGORY_CMS = 'cms';
    private const CATEGORY_CUSTOMER = 'customer';
    private const CATEGORY_CUSTOM_FIELDS = 'custom_fields';
    private const CATEGORY_DOCUMENTS = 'documents';
    private const CATEGORY_GOOGLE_SHOPPING = 'google_shopping';
    private const CATEGORY_IMPORT_EXPORT = 'import_export';
    private const CATEGORY_MAIL_TEMPLATES = 'mail_templates';
    private const CATEGORY_MEDIA = 'media';
    private const CATEGORY_NEWSLETTER = 'newsletter';
    private const CATEGORY_ORDER = 'order';
    private const CATEGORY_OTHER = 'other';
    private const CATEGORY_PAYMENT = 'payment';
    private const CATEGORY_PRODUCT = 'product';
    private const CATEGORY_PROMOTION = 'promotion';
    private const CATEGORY_RULES = 'rules';
    private const CATEGORY_CHANNEL = 'channel';
    private const CATEGORY_SETTINGS = 'settings';
    private const CATEGORY_SOCIAL_SHOPPING = 'social_shopping';
    private const CATEGORY_TAG = 'tag';
    private const CATEGORY_THEME = 'theme';
    private const CATEGORY_ADDITIONAL_PRIVILEGES = 'additional_privileges';

    /**
     * @see \HeyFrame\Storefront\Theme\ThemeDefinition::ENTITY_NAME
     */
    private const THEME_ENTITY_NAME = 'theme';
    /**
     * @see \HeyFrame\Storefront\Theme\Aggregate\ThemeTranslationDefinition::ENTITY_NAME
     */
    private const THEME_TRANSLATION_ENTITY_NAME = 'theme_translation';
    /**
     * @see \HeyFrame\Storefront\Theme\Aggregate\ThemeMediaDefinition::ENTITY_NAME
     */
    private const THEME_MEDIA_ENTITY_NAME = 'theme_media';
    /**
     * @see \HeyFrame\Storefront\Theme\Aggregate\ThemeChannelDefinition::ENTITY_NAME
     */
    private const THEME_CHANNEL_ENTITY_NAME = 'theme_channel';

    private const PERMISSION_CATEGORIES = [
        self::CATEGORY_ADMIN_USER => [
            IntegrationDefinition::ENTITY_NAME,
            UserDefinition::ENTITY_NAME,
            UserAccessKeyDefinition::ENTITY_NAME,
            UserRecoveryDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_APP => [
            TemplateDefinition::ENTITY_NAME,
            AppDefinition::ENTITY_NAME,
            AppTranslationDefinition::ENTITY_NAME,
            ActionButtonDefinition::ENTITY_NAME,
            ActionButtonTranslationDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_CATEGORY => [
            CategoryDefinition::ENTITY_NAME,
            CategoryTranslationDefinition::ENTITY_NAME,
            CategoryTagDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_CMS => [
            CmsBlockDefinition::ENTITY_NAME,
            CmsPageDefinition::ENTITY_NAME,
            CmsPageTranslationDefinition::ENTITY_NAME,
            CmsSectionDefinition::ENTITY_NAME,
            CmsSlotDefinition::ENTITY_NAME,
            CmsSlotTranslationDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_CUSTOMER => [
            CustomerDefinition::ENTITY_NAME,
            CustomerAddressDefinition::ENTITY_NAME,
            CustomerGroupDefinition::ENTITY_NAME,
            CustomerGroupTranslationDefinition::ENTITY_NAME,
            CustomerGroupRegistrationChannelDefinition::ENTITY_NAME,
            CustomerRecoveryDefinition::ENTITY_NAME,
            CustomerTagDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_CUSTOM_FIELDS => [
            CustomFieldDefinition::ENTITY_NAME,
            CustomFieldSetDefinition::ENTITY_NAME,
            CustomFieldSetRelationDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_DOCUMENTS => [
            DocumentDefinition::ENTITY_NAME,
            DocumentBaseConfigDefinition::ENTITY_NAME,
            DocumentBaseConfigChannelDefinition::ENTITY_NAME,
            DocumentTypeDefinition::ENTITY_NAME,
            DocumentTypeTranslationDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_GOOGLE_SHOPPING => [
            'swag_google_shopping_account',
            'swag_google_shopping_ads_account',
            'swag_google_shopping_list_ads_account',
            'swag_google_shopping_category',
            'swag_google_shopping_merchant_account',
        ],
        self::CATEGORY_IMPORT_EXPORT => [
            ImportExportFileDefinition::ENTITY_NAME,
            ImportExportLogDefinition::ENTITY_NAME,
            ImportExportProfileDefinition::ENTITY_NAME,
            ImportExportProfileTranslationDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_MAIL_TEMPLATES => [
            MailHeaderFooterDefinition::ENTITY_NAME,
            MailHeaderFooterTranslationDefinition::ENTITY_NAME,
            MailTemplateDefinition::ENTITY_NAME,
            MailTemplateTranslationDefinition::ENTITY_NAME,
            MailTemplateMediaDefinition::ENTITY_NAME,
            MailTemplateTypeDefinition::ENTITY_NAME,
            MailTemplateTypeTranslationDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_MEDIA => [
            MediaDefinition::ENTITY_NAME,
            MediaTranslationDefinition::ENTITY_NAME,
            MediaDefaultFolderDefinition::ENTITY_NAME,
            MediaFolderDefinition::ENTITY_NAME,
            MediaFolderConfigurationDefinition::ENTITY_NAME,
            MediaFolderConfigurationMediaThumbnailSizeDefinition::ENTITY_NAME,
            MediaTagDefinition::ENTITY_NAME,
            MediaThumbnailDefinition::ENTITY_NAME,
            MediaThumbnailSizeDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_NEWSLETTER => [
            NewsletterRecipientDefinition::ENTITY_NAME,
            NewsletterRecipientTagDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_ORDER => [
            OrderDefinition::ENTITY_NAME,
            OrderAddressDefinition::ENTITY_NAME,
            OrderCustomerDefinition::ENTITY_NAME,
            OrderDeliveryDefinition::ENTITY_NAME,
            OrderDeliveryPositionDefinition::ENTITY_NAME,
            OrderLineItemDefinition::ENTITY_NAME,
            OrderTagDefinition::ENTITY_NAME,
            OrderTransactionDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_PAYMENT => [
            PaymentMethodDefinition::ENTITY_NAME,
            PaymentMethodTranslationDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_PRODUCT => [
            ProductDefinition::ENTITY_NAME,
            ProductCategoryDefinition::ENTITY_NAME,
            ProductCategoryTreeDefinition::ENTITY_NAME,
            ProductConfiguratorSettingDefinition::ENTITY_NAME,
            ProductCrossSellingDefinition::ENTITY_NAME,
            ProductCrossSellingAssignedProductsDefinition::ENTITY_NAME,
            ProductCrossSellingTranslationDefinition::ENTITY_NAME,
            ProductExportDefinition::ENTITY_NAME,
            ProductKeywordDictionaryDefinition::ENTITY_NAME,
            ProductManufacturerDefinition::ENTITY_NAME,
            ProductManufacturerTranslationDefinition::ENTITY_NAME,
            ProductMediaDefinition::ENTITY_NAME,
            ProductOptionDefinition::ENTITY_NAME,
            ProductPriceDefinition::ENTITY_NAME,
            ProductPropertyDefinition::ENTITY_NAME,
            ProductReviewDefinition::ENTITY_NAME,
            ProductSearchKeywordDefinition::ENTITY_NAME,
            ProductStreamDefinition::ENTITY_NAME,
            ProductStreamFilterDefinition::ENTITY_NAME,
            ProductStreamTranslationDefinition::ENTITY_NAME,
            ProductTagDefinition::ENTITY_NAME,
            ProductVisibilityDefinition::ENTITY_NAME,
            ProductSortingDefinition::ENTITY_NAME,
            ProductTranslationDefinition::ENTITY_NAME,
            ProductFeatureSetDefinition::ENTITY_NAME,
            ProductFeatureSetTranslationDefinition::ENTITY_NAME,
            ProductCustomFieldSetDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_PROMOTION => [
            PromotionDefinition::ENTITY_NAME,
            PromotionTranslationDefinition::ENTITY_NAME,
            PromotionCartRuleDefinition::ENTITY_NAME,
            PromotionDiscountDefinition::ENTITY_NAME,
            PromotionDiscountPriceDefinition::ENTITY_NAME,
            PromotionDiscountRuleDefinition::ENTITY_NAME,
            PromotionIndividualCodeDefinition::ENTITY_NAME,
            PromotionOrderRuleDefinition::ENTITY_NAME,
            PromotionPersonaCustomerDefinition::ENTITY_NAME,
            PromotionPersonaRuleDefinition::ENTITY_NAME,
            PromotionChannelDefinition::ENTITY_NAME,
            PromotionSetGroupDefinition::ENTITY_NAME,
            PromotionSetGroupRuleDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_RULES => [
            RuleDefinition::ENTITY_NAME,
            RuleConditionDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_CHANNEL => [
            ChannelDefinition::ENTITY_NAME,
            ChannelAnalyticsDefinition::ENTITY_NAME,
            ChannelCountryDefinition::ENTITY_NAME,
            ChannelCurrencyDefinition::ENTITY_NAME,
            ChannelDomainDefinition::ENTITY_NAME,
            ChannelLanguageDefinition::ENTITY_NAME,
            ChannelPaymentMethodDefinition::ENTITY_NAME,
            ChannelShippingMethodDefinition::ENTITY_NAME,
            ChannelTranslationDefinition::ENTITY_NAME,
            ChannelTypeDefinition::ENTITY_NAME,
            ChannelTypeTranslationDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_SETTINGS => [
            CountryDefinition::ENTITY_NAME,
            CountryStateDefinition::ENTITY_NAME,
            CurrencyDefinition::ENTITY_NAME,
            DeliveryTimeDefinition::ENTITY_NAME,
            LanguageDefinition::ENTITY_NAME,
            LocaleDefinition::ENTITY_NAME,
            LocaleTranslationDefinition::ENTITY_NAME,
            NumberRangeDefinition::ENTITY_NAME,
            NumberRangeChannelDefinition::ENTITY_NAME,
            NumberRangeStateDefinition::ENTITY_NAME,
            NumberRangeTypeDefinition::ENTITY_NAME,
            SalutationDefinition::ENTITY_NAME,
            SalutationTranslationDefinition::ENTITY_NAME,
            SeoUrlDefinition::ENTITY_NAME,
            SeoUrlTemplateDefinition::ENTITY_NAME,
            StateMachineDefinition::ENTITY_NAME,
            StateMachineHistoryDefinition::ENTITY_NAME,
            StateMachineStateDefinition::ENTITY_NAME,
            StateMachineStateTranslationDefinition::ENTITY_NAME,
            StateMachineTransitionDefinition::ENTITY_NAME,
            StateMachineTranslationDefinition::ENTITY_NAME,
            SystemConfigDefinition::ENTITY_NAME,
            TaxDefinition::ENTITY_NAME,
            TaxRuleDefinition::ENTITY_NAME,
            TaxRuleTypeDefinition::ENTITY_NAME,
            UnitDefinition::ENTITY_NAME,
            VersionDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_SOCIAL_SHOPPING => [
            'swag_social_shopping_channel',
            'swag_social_shopping_product_error',
        ],
        self::CATEGORY_TAG => [
            TagDefinition::ENTITY_NAME,
        ],
        self::CATEGORY_THEME => [
            self::THEME_ENTITY_NAME,
            self::THEME_TRANSLATION_ENTITY_NAME,
            self::THEME_MEDIA_ENTITY_NAME,
            self::THEME_CHANNEL_ENTITY_NAME,
        ],
        self::CATEGORY_ADDITIONAL_PRIVILEGES => [
            'additional_privileges',
        ],
    ];

    public static function isInCategory(string $entity, string $category): bool
    {
        if ($category === self::CATEGORY_OTHER) {
            $allCategories = array_merge(...array_values(self::PERMISSION_CATEGORIES));

            return !\in_array($entity, $allCategories, true);
        }

        return \in_array($entity, self::PERMISSION_CATEGORIES[$category], true);
    }

    /**
     * @return string[]
     */
    public static function getCategoryNames(): array
    {
        $categories = array_keys(self::PERMISSION_CATEGORIES);
        $categories[] = self::CATEGORY_OTHER;

        return $categories;
    }
}
