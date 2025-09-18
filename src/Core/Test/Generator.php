<?php declare(strict_types=1);

namespace HeyFrame\Core\Test;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Payment\Cart\PaymentHandler\DefaultPayment;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\Channel\Context\LanguageInfo;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateEntity;
use HeyFrame\Core\System\Country\CountryEntity;
use HeyFrame\Core\System\Currency\CurrencyEntity;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
class Generator extends TestCase
{
    final public const TOKEN = 'test-token';
    final public const DOMAIN = 'test-domain';
    final public const NAVIGATION_CATEGORY = 'f8466865cc6a45e48ed98dd2f6a0a293';
    final public const TAX = 'c725e107825c4c7281673aeea66ed67e';
    final public const TAX_RATE = 19.0;
    final public const PAYMENT_METHOD = 'cce0e1ca23de4c55868ce057f628c349';
    final public const SHIPPING_METHOD = '37dbe80c5cbb4852a97cb742ed04ba41';
    final public const COUNTRY = 'd4eb3205dd9444169b3f60c056c313a1';
    final public const COUNTRY_STATE = '119d6e30fc4f468daa88ff5b413e9322';
    final public const CUSTOMER_ADDRESS = '08f1594313494c3e9eb57bb53486fe61';
    final public const CUSTOMER = '42d58aa78cf14851968a786a66bab93a';
    final public const LANGUAGE_INFO_NAME = 'English';
    final public const LANGUAGE_INFO_LOCALE_CODE = 'en-GB';

    /**
     * @param array<string, string[]> $areaRuleIds
     * @param array<array-key, mixed> $overrides
     */
    public static function generateChannelContext(
        ?Context $baseContext = null,
        ?string $token = null,
        ?string $domainId = null,
        ?ChannelEntity $channel = null,
        ?CurrencyEntity $currency = null,
        ?CustomerGroupEntity $currentCustomerGroup = null,
        ?PaymentMethodEntity $paymentMethod = null,
        ?CustomerEntity $customer = null,
        ?CashRoundingConfig $itemRounding = null,
        ?CashRoundingConfig $totalRounding = null,
        ?array $areaRuleIds = [],
        ?LanguageInfo $languageInfo = null,
        ?CountryEntity $country = null,
        ?CountryStateEntity $countryState = null,
        ?array $overrides = [],
    ): ChannelContext {
        $baseContext ??= Context::createDefaultContext();

        $token ??= self::TOKEN;

        $domainId ??= self::DOMAIN;

        if (!$channel) {
            $channel = new ChannelEntity();
            $channel->setId(TestDefaults::CHANNEL);
            $channel->setNavigationCategoryId(self::NAVIGATION_CATEGORY);
            $channel->setNavigationCategoryDepth(2);
        }

        if (!$currency) {
            $currency = new CurrencyEntity();
            $currency->setId($baseContext->getCurrencyId());
            $currency->setFactor($baseContext->getCurrencyFactor());
        }

        if (!$currentCustomerGroup) {
            $currentCustomerGroup = new CustomerGroupEntity();
            $currentCustomerGroup->setId(TestDefaults::FALLBACK_CUSTOMER_GROUP);
        }

        if (!$paymentMethod) {
            $paymentMethod = new PaymentMethodEntity();
            $paymentMethod->setId(self::PAYMENT_METHOD);
            $paymentMethod->setHandlerIdentifier(DefaultPayment::class);
            $paymentMethod->setName('Generated Payment');
            $paymentMethod->setActive(true);
        }

        $channel->setPaymentMethodIds([$paymentMethod->getId()]);
        $channel->setPaymentMethodId($paymentMethod->getId());
        $channel->setPaymentMethod($paymentMethod);

        if (!$customer) {
            $customer = new CustomerEntity();
            $customer->setId(self::CUSTOMER);
            $customer->setGroupId($currentCustomerGroup->getId());
            $customer->setGroup($currentCustomerGroup);
            $customer->setChannelId($channel->getId());
            $customer->setChannel($channel);
        }

        $itemRounding ??= clone $baseContext->getRounding();

        $totalRounding ??= clone $baseContext->getRounding();

        $areaRuleIds ??= [];

        $languageInfo ??= self::createLanguageInfo();

        $channelContext = new ChannelContext(
            $baseContext,
            $token,
            $domainId,
            $channel,
            $currency,
            $currentCustomerGroup,
            $paymentMethod,
            $customer,
            $itemRounding,
            $totalRounding,
            $languageInfo,
            $areaRuleIds,
        );

        if ($overrides) {
            $channelContext->assign($overrides);
        }

        return $channelContext;
    }

    public static function createCart(): Cart
    {
        $cart = new Cart('test');
        $cart->setLineItems(
            new LineItemCollection([
                (new LineItem('A', 'product', 'A', 27))
                    ->setPrice(new CalculatedPrice(10, 270, 27)),
                (new LineItem('B', 'test', 'B', 5))
                    ->setGood(false)
                    ->setPrice(new CalculatedPrice(0, 0)),
            ])
        );
        $cart->setPrice(
            new CartPrice(
                275.0,
                275.0,
                0,
            )
        );

        return $cart;
    }

    public static function createLanguageInfo(
        ?string $id = null,
        ?string $name = null,
    ): LanguageInfo {
        return new LanguageInfo(
            $id ?? Defaults::LANGUAGE_SYSTEM,
            $name ?? self::LANGUAGE_INFO_NAME,
        );
    }
}
