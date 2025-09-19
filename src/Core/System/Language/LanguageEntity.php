<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Language;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroupTranslation\CustomerGroupTranslationCollection;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Payment\Aggregate\PaymentMethodTranslation\PaymentMethodTranslationCollection;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionTranslation\PromotionTranslationCollection;
use HeyFrame\Core\Content\Category\Aggregate\CategoryTranslation\CategoryTranslationCollection;
use HeyFrame\Core\Content\Cms\Aggregate\CmsPageTranslation\CmsPageTranslationEntity;
use HeyFrame\Core\Content\Media\Aggregate\MediaTranslation\MediaTranslationCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductTranslation\ProductTranslationCollection;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOptionTranslation\PropertyGroupOptionTranslationCollection;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupTranslation\PropertyGroupTranslationCollection;
use HeyFrame\Core\Framework\App\Aggregate\ActionButtonTranslation\ActionButtonTranslationCollection;
use HeyFrame\Core\Framework\App\Aggregate\AppScriptConditionTranslation\AppScriptConditionTranslationCollection;
use HeyFrame\Core\Framework\App\Aggregate\AppTranslation\AppTranslationCollection;
use HeyFrame\Core\Framework\App\Aggregate\FlowActionTranslation\AppFlowActionTranslationCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Aggregate\PluginTranslation\PluginTranslationCollection;
use HeyFrame\Core\Framework\Struct\Collection;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainCollection;
use HeyFrame\Core\System\Channel\Aggregate\ChannelTranslation\ChannelTranslationCollection;
use HeyFrame\Core\System\Channel\Aggregate\ChannelTypeTranslation\ChannelTypeTranslationCollection;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Country\Aggregate\CountryStateTranslation\CountryStateTranslationCollection;
use HeyFrame\Core\System\Country\Aggregate\CountryTranslation\CountryTranslationCollection;
use HeyFrame\Core\System\Currency\Aggregate\CurrencyTranslation\CurrencyTranslationCollection;
use HeyFrame\Core\System\Locale\Aggregate\LocaleTranslation\LocaleTranslationCollection;
use HeyFrame\Core\System\Locale\LocaleEntity;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeTranslation\NumberRangeTranslationCollection;
use HeyFrame\Core\System\NumberRange\Aggregate\NumberRangeTypeTranslation\NumberRangeTypeTranslationCollection;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateTranslationCollection;
use HeyFrame\Core\System\StateMachine\StateMachineTranslationCollection;

#[Package('fundamentals@discovery')]
class LanguageEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected ?string $parentId = null;

    protected string $localeId;

    protected ?string $translationCodeId = null;

    protected ?LocaleEntity $translationCode = null;

    protected string $name;

    protected bool $active;

    protected ?LocaleEntity $locale = null;

    protected ?LanguageEntity $parent = null;

    protected ?LanguageCollection $children = null;

    protected ?ChannelCollection $channels = null;

    protected ?CustomerCollection $customers = null;

    protected ?ChannelCollection $channelDefaultAssignments = null;

    protected ?CategoryTranslationCollection $categoryTranslations = null;

    protected ?CountryStateTranslationCollection $countryStateTranslations = null;

    protected ?CountryTranslationCollection $countryTranslations = null;

    protected ?CurrencyTranslationCollection $currencyTranslations = null;

    protected ?CustomerGroupTranslationCollection $customerGroupTranslations = null;

    protected ?LocaleTranslationCollection $localeTranslations = null;

    protected ?MediaTranslationCollection $mediaTranslations = null;

    protected ?PaymentMethodTranslationCollection $paymentMethodTranslations = null;

    protected ?ProductTranslationCollection $productTranslations = null;

    protected ?PropertyGroupTranslationCollection $propertyGroupTranslations = null;

    protected ?PropertyGroupOptionTranslationCollection $propertyGroupOptionTranslations = null;

    protected ?ChannelTranslationCollection $channelTranslations = null;

    protected ?ChannelTypeTranslationCollection $channelTypeTranslations = null;

    protected ?ChannelDomainCollection $channelDomains = null;

    protected ?PluginTranslationCollection $pluginTranslations = null;

    protected ?StateMachineTranslationCollection $stateMachineTranslations = null;

    protected ?StateMachineStateTranslationCollection $stateMachineStateTranslations = null;

    /**
     * @var EntityCollection<CmsPageTranslationEntity>|null
     */
    protected ?EntityCollection $cmsPageTranslations = null;

    protected ?OrderCollection $orders = null;

    protected ?NumberRangeTypeTranslationCollection $numberRangeTypeTranslations = null;

    protected ?PromotionTranslationCollection $promotionTranslations = null;

    protected ?NumberRangeTranslationCollection $numberRangeTranslations = null;

    protected ?AppTranslationCollection $appTranslations = null;

    protected ?ActionButtonTranslationCollection $actionButtonTranslations = null;

    protected ?AppScriptConditionTranslationCollection $appScriptConditionTranslations = null;

    protected ?AppFlowActionTranslationCollection $appFlowActionTranslations = null;

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getLocaleId(): string
    {
        return $this->localeId;
    }

    public function setLocaleId(string $localeId): void
    {
        $this->localeId = $localeId;
    }

    public function getTranslationCodeId(): ?string
    {
        return $this->translationCodeId;
    }

    public function setTranslationCodeId(?string $translationCodeId): void
    {
        $this->translationCodeId = $translationCodeId;
    }

    public function getTranslationCode(): ?LocaleEntity
    {
        return $this->translationCode;
    }

    public function setTranslationCode(?LocaleEntity $translationCode): void
    {
        $this->translationCode = $translationCode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getLocale(): ?LocaleEntity
    {
        return $this->locale;
    }

    public function setLocale(LocaleEntity $locale): void
    {
        $this->locale = $locale;
    }

    public function getParent(): ?LanguageEntity
    {
        return $this->parent;
    }

    public function setParent(LanguageEntity $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): ?LanguageCollection
    {
        return $this->children;
    }

    public function setChildren(LanguageCollection $children): void
    {
        $this->children = $children;
    }

    public function getCategoryTranslations(): ?CategoryTranslationCollection
    {
        return $this->categoryTranslations;
    }

    public function setCategoryTranslations(CategoryTranslationCollection $categoryTranslations): void
    {
        $this->categoryTranslations = $categoryTranslations;
    }

    public function getCountryStateTranslations(): ?CountryStateTranslationCollection
    {
        return $this->countryStateTranslations;
    }

    public function setCountryStateTranslations(CountryStateTranslationCollection $countryStateTranslations): void
    {
        $this->countryStateTranslations = $countryStateTranslations;
    }

    public function getCountryTranslations(): ?CountryTranslationCollection
    {
        return $this->countryTranslations;
    }

    public function setCountryTranslations(CountryTranslationCollection $countryTranslations): void
    {
        $this->countryTranslations = $countryTranslations;
    }

    public function getCurrencyTranslations(): ?CurrencyTranslationCollection
    {
        return $this->currencyTranslations;
    }

    public function setCurrencyTranslations(CurrencyTranslationCollection $currencyTranslations): void
    {
        $this->currencyTranslations = $currencyTranslations;
    }

    public function getCustomerGroupTranslations(): ?CustomerGroupTranslationCollection
    {
        return $this->customerGroupTranslations;
    }

    public function setCustomerGroupTranslations(CustomerGroupTranslationCollection $customerGroupTranslations): void
    {
        $this->customerGroupTranslations = $customerGroupTranslations;
    }

    public function getLocaleTranslations(): ?LocaleTranslationCollection
    {
        return $this->localeTranslations;
    }

    public function setLocaleTranslations(LocaleTranslationCollection $localeTranslations): void
    {
        $this->localeTranslations = $localeTranslations;
    }

    public function getMediaTranslations(): ?MediaTranslationCollection
    {
        return $this->mediaTranslations;
    }

    public function setMediaTranslations(MediaTranslationCollection $mediaTranslations): void
    {
        $this->mediaTranslations = $mediaTranslations;
    }

    public function getPaymentMethodTranslations(): ?PaymentMethodTranslationCollection
    {
        return $this->paymentMethodTranslations;
    }

    public function setPaymentMethodTranslations(PaymentMethodTranslationCollection $paymentMethodTranslations): void
    {
        $this->paymentMethodTranslations = $paymentMethodTranslations;
    }

    public function getProductTranslations(): ?ProductTranslationCollection
    {
        return $this->productTranslations;
    }

    public function setProductTranslations(ProductTranslationCollection $productTranslations): void
    {
        $this->productTranslations = $productTranslations;
    }

    public function getChannels(): ?ChannelCollection
    {
        return $this->channels;
    }

    public function setChannels(ChannelCollection $channels): void
    {
        $this->channels = $channels;
    }

    public function getChannelDefaultAssignments(): ?ChannelCollection
    {
        return $this->channelDefaultAssignments;
    }

    public function getCustomers(): ?CustomerCollection
    {
        return $this->customers;
    }

    public function setCustomers(CustomerCollection $customers): void
    {
        $this->customers = $customers;
    }

    public function setChannelDefaultAssignments(ChannelCollection $channelDefaultAssignments): void
    {
        $this->channelDefaultAssignments = $channelDefaultAssignments;
    }

    public function getPropertyGroupTranslations(): ?PropertyGroupTranslationCollection
    {
        return $this->propertyGroupTranslations;
    }

    public function setPropertyGroupTranslations(PropertyGroupTranslationCollection $propertyGroupTranslations): void
    {
        $this->propertyGroupTranslations = $propertyGroupTranslations;
    }

    public function getPropertyGroupOptionTranslations(): ?PropertyGroupOptionTranslationCollection
    {
        return $this->propertyGroupOptionTranslations;
    }

    public function setPropertyGroupOptionTranslations(PropertyGroupOptionTranslationCollection $propertyGroupOptionTranslationCollection): void
    {
        $this->propertyGroupOptionTranslations = $propertyGroupOptionTranslationCollection;
    }

    public function getChannelTranslations(): ?ChannelTranslationCollection
    {
        return $this->channelTranslations;
    }

    public function setChannelTranslations(ChannelTranslationCollection $channelTranslations): void
    {
        $this->channelTranslations = $channelTranslations;
    }

    public function getChannelTypeTranslations(): ?ChannelTypeTranslationCollection
    {
        return $this->channelTypeTranslations;
    }

    public function setChannelTypeTranslations(ChannelTypeTranslationCollection $channelTypeTranslations): void
    {
        $this->channelTypeTranslations = $channelTypeTranslations;
    }

    public function getChannelDomains(): ?ChannelDomainCollection
    {
        return $this->channelDomains;
    }

    public function setChannelDomains(ChannelDomainCollection $channelDomains): void
    {
        $this->channelDomains = $channelDomains;
    }

    public function getPluginTranslations(): ?PluginTranslationCollection
    {
        return $this->pluginTranslations;
    }

    public function setPluginTranslations(PluginTranslationCollection $pluginTranslations): void
    {
        $this->pluginTranslations = $pluginTranslations;
    }

    /**
     * @return StateMachineTranslationCollection|null
     */
    public function getStateMachineTranslations(): ?Collection
    {
        return $this->stateMachineTranslations;
    }

    /**
     * @param StateMachineTranslationCollection $stateMachineTranslations
     */
    public function setStateMachineTranslations(Collection $stateMachineTranslations): void
    {
        $this->stateMachineTranslations = $stateMachineTranslations;
    }

    /**
     * @return StateMachineStateTranslationCollection|null
     */
    public function getStateMachineStateTranslations(): ?Collection
    {
        return $this->stateMachineStateTranslations;
    }

    /**
     * @param StateMachineStateTranslationCollection $stateMachineStateTranslations
     */
    public function setStateMachineStateTranslations(Collection $stateMachineStateTranslations): void
    {
        $this->stateMachineStateTranslations = $stateMachineStateTranslations;
    }

    /**
     * @return EntityCollection<CmsPageTranslationEntity>|null
     */
    public function getCmsPageTranslations(): ?Collection
    {
        return $this->cmsPageTranslations;
    }

    /**
     * @param EntityCollection<CmsPageTranslationEntity> $cmsPageTranslations
     */
    public function setCmsPageTranslations(Collection $cmsPageTranslations): void
    {
        $this->cmsPageTranslations = $cmsPageTranslations;
    }

    public function getOrders(): ?OrderCollection
    {
        return $this->orders;
    }

    public function setOrders(OrderCollection $orders): void
    {
        $this->orders = $orders;
    }

    public function getNumberRangeTypeTranslations(): ?NumberRangeTypeTranslationCollection
    {
        return $this->numberRangeTypeTranslations;
    }

    public function setNumberRangeTypeTranslations(NumberRangeTypeTranslationCollection $numberRangeTypeTranslations): void
    {
        $this->numberRangeTypeTranslations = $numberRangeTypeTranslations;
    }

    public function getPromotionTranslations(): ?PromotionTranslationCollection
    {
        return $this->promotionTranslations;
    }

    public function setPromotionTranslations(PromotionTranslationCollection $promotionTranslations): void
    {
        $this->promotionTranslations = $promotionTranslations;
    }

    public function getNumberRangeTranslations(): ?NumberRangeTranslationCollection
    {
        return $this->numberRangeTranslations;
    }

    public function setNumberRangeTranslations(NumberRangeTranslationCollection $numberRangeTranslations): void
    {
        $this->numberRangeTranslations = $numberRangeTranslations;
    }

    public function getAppTranslations(): ?AppTranslationCollection
    {
        return $this->appTranslations;
    }

    public function setAppTranslations(AppTranslationCollection $appTranslations): void
    {
        $this->appTranslations = $appTranslations;
    }

    public function getActionButtonTranslations(): ?ActionButtonTranslationCollection
    {
        return $this->actionButtonTranslations;
    }

    public function setActionButtonTranslations(ActionButtonTranslationCollection $actionButtonTranslations): void
    {
        $this->actionButtonTranslations = $actionButtonTranslations;
    }

    public function getAppScriptConditionTranslations(): ?AppScriptConditionTranslationCollection
    {
        return $this->appScriptConditionTranslations;
    }

    public function setAppScriptConditionTranslations(AppScriptConditionTranslationCollection $appScriptConditionTranslations): void
    {
        $this->appScriptConditionTranslations = $appScriptConditionTranslations;
    }

    public function getAppFlowActionTranslations(): ?AppFlowActionTranslationCollection
    {
        return $this->appFlowActionTranslations;
    }

    public function setAppFlowActionTranslations(AppFlowActionTranslationCollection $appFlowActionTranslations): void
    {
        $this->appFlowActionTranslations = $appFlowActionTranslations;
    }

    public function getApiAlias(): string
    {
        return 'language';
    }
}
