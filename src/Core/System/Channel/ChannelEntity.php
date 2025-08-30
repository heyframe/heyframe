<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionChannel\PromotionChannelCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Aggregate\ChannelAnalytics\ChannelAnalyticsEntity;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainCollection;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainEntity;
use HeyFrame\Core\System\Channel\Aggregate\ChannelTranslation\ChannelTranslationCollection;
use HeyFrame\Core\System\Channel\Aggregate\ChannelType\ChannelTypeEntity;
use HeyFrame\Core\System\Country\CountryCollection;
use HeyFrame\Core\System\Country\CountryEntity;
use HeyFrame\Core\System\Currency\CurrencyCollection;
use HeyFrame\Core\System\Currency\CurrencyEntity;
use HeyFrame\Core\System\Language\LanguageCollection;
use HeyFrame\Core\System\Language\LanguageEntity;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeChannel\NumberRangeChannelCollection;
use HeyFrame\Core\System\SystemConfig\SystemConfigCollection;

#[Package('discovery')]
class ChannelEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $typeId;

    protected string $languageId;

    protected string $currencyId;

    protected string $paymentMethodId;

    protected string $countryId;

    protected string $navigationCategoryId;

    protected string $navigationCategoryVersionId;

    protected int $navigationCategoryDepth;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $homeSlotConfig = null;

    protected ?string $homeCmsPageId = null;

    protected ?string $homeCmsPageVersionId = null;

    protected bool $homeEnabled;

    protected ?string $homeName = null;

    protected ?string $homeMetaTitle = null;

    protected ?string $homeMetaDescription = null;

    protected ?string $homeKeywords = null;

    protected ?string $footerCategoryId = null;

    protected ?string $footerCategoryVersionId = null;

    protected ?string $serviceCategoryId = null;

    protected ?string $serviceCategoryVersionId = null;

    protected ?string $name = null;

    protected ?string $shortName = null;

    protected string $accessKey;

    protected ?CurrencyCollection $currencies = null;

    protected ?LanguageCollection $languages = null;

    /**
     * @var array<mixed>|null
     */
    protected ?array $configuration = null;

    protected bool $active;

    protected bool $maintenance;

    /**
     * @var array<mixed>|null
     */
    protected ?array $maintenanceIpWhitelist = null;

    protected string $taxCalculationType;

    protected ?ChannelTypeEntity $type = null;

    protected ?CurrencyEntity $currency = null;

    protected ?LanguageEntity $language = null;

    protected ?PaymentMethodEntity $paymentMethod = null;

    protected ?CountryEntity $country = null;

    protected ?OrderCollection $orders = null;

    protected ?CustomerCollection $customers = null;

    protected ?CountryCollection $countries = null;

    protected ?PaymentMethodCollection $paymentMethods = null;

    protected ?ChannelTranslationCollection $translations = null;

    protected ?ChannelDomainCollection $domains = null;

    protected ?SystemConfigCollection $systemConfigs = null;

    protected ?ProductVisibilityCollection $productVisibilities = null;

    protected ?string $mailHeaderFooterId = null;

    protected ?NumberRangeChannelCollection $numberRangeChannels = null;

    protected string $customerGroupId;

    protected ?CustomerGroupEntity $customerGroup = null;

    protected ?PromotionChannelCollection $promotionChannels = null;

    /**
     * @var list<string>|null
     */
    protected ?array $paymentMethodIds = null;

    protected bool $hreflangActive;

    protected ?string $hreflangDefaultDomainId = null;

    protected ?ChannelDomainEntity $hreflangDefaultDomain = null;

    protected ?string $analyticsId = null;

    protected ?ChannelAnalyticsEntity $analytics = null;

    protected ?CustomerGroupCollection $customerGroupsRegistrations = null;

    protected ?CustomerCollection $boundCustomers = null;

    public function getMailHeaderFooterId(): ?string
    {
        return $this->mailHeaderFooterId;
    }

    public function setMailHeaderFooterId(string $mailHeaderFooterId): void
    {
        $this->mailHeaderFooterId = $mailHeaderFooterId;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(string $paymentMethodId): void
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    public function getCountryId(): string
    {
        return $this->countryId;
    }

    public function setCountryId(string $countryId): void
    {
        $this->countryId = $countryId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): void
    {
        $this->shortName = $shortName;
    }

    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    public function setAccessKey(string $accessKey): void
    {
        $this->accessKey = $accessKey;
    }

    public function getCurrencies(): ?CurrencyCollection
    {
        return $this->currencies;
    }

    public function setCurrencies(CurrencyCollection $currencies): void
    {
        $this->currencies = $currencies;
    }

    public function getLanguages(): ?LanguageCollection
    {
        return $this->languages;
    }

    public function setLanguages(LanguageCollection $languages): void
    {
        $this->languages = $languages;
    }

    /**
     * @return array<mixed>|null
     */
    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    /**
     * @param array<mixed> $configuration
     */
    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function isMaintenance(): bool
    {
        return $this->maintenance;
    }

    public function setMaintenance(bool $maintenance): void
    {
        $this->maintenance = $maintenance;
    }

    /**
     * @return array<mixed>|null
     */
    public function getMaintenanceIpWhitelist(): ?array
    {
        return $this->maintenanceIpWhitelist;
    }

    /**
     * @param array<mixed>|null $maintenanceIpWhitelist
     */
    public function setMaintenanceIpWhitelist(?array $maintenanceIpWhitelist): void
    {
        $this->maintenanceIpWhitelist = $maintenanceIpWhitelist;
    }

    public function getCurrency(): ?CurrencyEntity
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyEntity $currency): void
    {
        $this->currency = $currency;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(LanguageEntity $language): void
    {
        $this->language = $language;
    }

    public function getPaymentMethod(): ?PaymentMethodEntity
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentMethodEntity $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getCountry(): ?CountryEntity
    {
        return $this->country;
    }

    public function setCountry(CountryEntity $country): void
    {
        $this->country = $country;
    }

    public function getOrders(): ?OrderCollection
    {
        return $this->orders;
    }

    public function setOrders(OrderCollection $orders): void
    {
        $this->orders = $orders;
    }

    public function getCustomers(): ?CustomerCollection
    {
        return $this->customers;
    }

    public function setCustomers(CustomerCollection $customers): void
    {
        $this->customers = $customers;
    }

    public function getTypeId(): string
    {
        return $this->typeId;
    }

    public function setTypeId(string $typeId): void
    {
        $this->typeId = $typeId;
    }

    public function getType(): ?ChannelTypeEntity
    {
        return $this->type;
    }

    public function setType(ChannelTypeEntity $type): void
    {
        $this->type = $type;
    }

    public function getCountries(): ?CountryCollection
    {
        return $this->countries;
    }

    public function setCountries(CountryCollection $countries): void
    {
        $this->countries = $countries;
    }

    public function getTranslations(): ?ChannelTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(ChannelTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getPaymentMethods(): ?PaymentMethodCollection
    {
        return $this->paymentMethods;
    }

    public function setPaymentMethods(PaymentMethodCollection $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }

    public function getDomains(): ?ChannelDomainCollection
    {
        return $this->domains;
    }

    public function setDomains(ChannelDomainCollection $domains): void
    {
        $this->domains = $domains;
    }

    public function getSystemConfigs(): ?SystemConfigCollection
    {
        return $this->systemConfigs;
    }

    public function setSystemConfigs(SystemConfigCollection $systemConfigs): void
    {
        $this->systemConfigs = $systemConfigs;
    }

    public function getNavigationCategoryId(): string
    {
        return $this->navigationCategoryId;
    }

    public function setNavigationCategoryId(string $navigationCategoryId): void
    {
        $this->navigationCategoryId = $navigationCategoryId;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getHomeSlotConfig(): ?array
    {
        return $this->homeSlotConfig;
    }

    /**
     * @param array<string, mixed>|null $homeSlotConfig
     */
    public function setHomeSlotConfig(?array $homeSlotConfig): void
    {
        $this->homeSlotConfig = $homeSlotConfig;
    }

    public function getHomeCmsPageId(): ?string
    {
        return $this->homeCmsPageId;
    }

    public function setHomeCmsPageId(?string $homeCmsPageId): void
    {
        $this->homeCmsPageId = $homeCmsPageId;
    }

    public function getHomeEnabled(): bool
    {
        return $this->homeEnabled;
    }

    public function setHomeEnabled(bool $homeEnabled): void
    {
        $this->homeEnabled = $homeEnabled;
    }

    public function getHomeName(): ?string
    {
        return $this->homeName;
    }

    public function setHomeName(?string $homeName): void
    {
        $this->homeName = $homeName;
    }

    public function getHomeMetaTitle(): ?string
    {
        return $this->homeMetaTitle;
    }

    public function setHomeMetaTitle(?string $homeMetaTitle): void
    {
        $this->homeMetaTitle = $homeMetaTitle;
    }

    public function getHomeMetaDescription(): ?string
    {
        return $this->homeMetaDescription;
    }

    public function setHomeMetaDescription(?string $homeMetaDescription): void
    {
        $this->homeMetaDescription = $homeMetaDescription;
    }

    public function getHomeKeywords(): ?string
    {
        return $this->homeKeywords;
    }

    public function setHomeKeywords(?string $homeKeywords): void
    {
        $this->homeKeywords = $homeKeywords;
    }

    public function getProductVisibilities(): ?ProductVisibilityCollection
    {
        return $this->productVisibilities;
    }

    public function setProductVisibilities(ProductVisibilityCollection $productVisibilities): void
    {
        $this->productVisibilities = $productVisibilities;
    }

    public function getCustomerGroupId(): string
    {
        return $this->customerGroupId;
    }

    public function setCustomerGroupId(string $customerGroupId): void
    {
        $this->customerGroupId = $customerGroupId;
    }

    public function getCustomerGroup(): ?CustomerGroupEntity
    {
        return $this->customerGroup;
    }

    public function setCustomerGroup(CustomerGroupEntity $customerGroup): void
    {
        $this->customerGroup = $customerGroup;
    }

    public function getPromotionChannels(): ?PromotionChannelCollection
    {
        return $this->promotionChannels;
    }

    public function setPromotionChannels(PromotionChannelCollection $promotionChannels): void
    {
        $this->promotionChannels = $promotionChannels;
    }

    public function getNumberRangeChannels(): ?NumberRangeChannelCollection
    {
        return $this->numberRangeChannels;
    }

    public function setNumberRangeChannels(NumberRangeChannelCollection $numberRangeChannels): void
    {
        $this->numberRangeChannels = $numberRangeChannels;
    }

    public function getFooterCategoryId(): ?string
    {
        return $this->footerCategoryId;
    }

    public function setFooterCategoryId(string $footerCategoryId): void
    {
        $this->footerCategoryId = $footerCategoryId;
    }

    public function getServiceCategoryId(): ?string
    {
        return $this->serviceCategoryId;
    }

    public function setServiceCategoryId(string $serviceCategoryId): void
    {
        $this->serviceCategoryId = $serviceCategoryId;
    }

    /**
     * @return list<string>|null
     */
    public function getPaymentMethodIds(): ?array
    {
        return $this->paymentMethodIds;
    }

    /**
     * @param list<string> $paymentMethodIds
     */
    public function setPaymentMethodIds(array $paymentMethodIds): void
    {
        $this->paymentMethodIds = $paymentMethodIds;
    }

    public function getNavigationCategoryDepth(): int
    {
        return $this->navigationCategoryDepth;
    }

    public function setNavigationCategoryDepth(int $navigationCategoryDepth): void
    {
        $this->navigationCategoryDepth = $navigationCategoryDepth;
    }

    public function isHreflangActive(): bool
    {
        return $this->hreflangActive;
    }

    public function setHreflangActive(bool $hreflangActive): void
    {
        $this->hreflangActive = $hreflangActive;
    }

    public function getHreflangDefaultDomainId(): ?string
    {
        return $this->hreflangDefaultDomainId;
    }

    public function setHreflangDefaultDomainId(?string $hreflangDefaultDomainId): void
    {
        $this->hreflangDefaultDomainId = $hreflangDefaultDomainId;
    }

    public function getHreflangDefaultDomain(): ?ChannelDomainEntity
    {
        return $this->hreflangDefaultDomain;
    }

    public function setHreflangDefaultDomain(?ChannelDomainEntity $hreflangDefaultDomain): void
    {
        $this->hreflangDefaultDomain = $hreflangDefaultDomain;
    }

    public function getAnalyticsId(): ?string
    {
        return $this->analyticsId;
    }

    public function setAnalyticsId(?string $analyticsId): void
    {
        $this->analyticsId = $analyticsId;
    }

    public function getAnalytics(): ?ChannelAnalyticsEntity
    {
        return $this->analytics;
    }

    public function setAnalytics(?ChannelAnalyticsEntity $analytics): void
    {
        $this->analytics = $analytics;
    }

    public function getTaxCalculationType(): string
    {
        return $this->taxCalculationType;
    }

    public function setTaxCalculationType(string $taxCalculationType): void
    {
        $this->taxCalculationType = $taxCalculationType;
    }

    public function getCustomerGroupsRegistrations(): ?CustomerGroupCollection
    {
        return $this->customerGroupsRegistrations;
    }

    public function setCustomerGroupsRegistrations(CustomerGroupCollection $customerGroupsRegistrations): void
    {
        $this->customerGroupsRegistrations = $customerGroupsRegistrations;
    }

    public function getBoundCustomers(): ?CustomerCollection
    {
        return $this->boundCustomers;
    }

    public function setBoundCustomers(CustomerCollection $boundCustomers): void
    {
        $this->boundCustomers = $boundCustomers;
    }

    public function getNavigationCategoryVersionId(): string
    {
        return $this->navigationCategoryVersionId;
    }

    public function setNavigationCategoryVersionId(string $navigationCategoryVersionId): void
    {
        $this->navigationCategoryVersionId = $navigationCategoryVersionId;
    }

    public function getHomeCmsPageVersionId(): ?string
    {
        return $this->homeCmsPageVersionId;
    }

    public function setHomeCmsPageVersionId(?string $homeCmsPageVersionId): void
    {
        $this->homeCmsPageVersionId = $homeCmsPageVersionId;
    }

    public function getFooterCategoryVersionId(): ?string
    {
        return $this->footerCategoryVersionId;
    }

    public function setFooterCategoryVersionId(?string $footerCategoryVersionId): void
    {
        $this->footerCategoryVersionId = $footerCategoryVersionId;
    }

    public function getServiceCategoryVersionId(): ?string
    {
        return $this->serviceCategoryVersionId;
    }

    public function setServiceCategoryVersionId(?string $serviceCategoryVersionId): void
    {
        $this->serviceCategoryVersionId = $serviceCategoryVersionId;
    }
}
