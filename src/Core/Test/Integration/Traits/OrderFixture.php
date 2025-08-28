<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Integration\Traits;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use HeyFrame\Core\Checkout\Order\OrderStates;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\BasicTestDataBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\StateMachine\Loader\InitialStateIdLoader;
use HeyFrame\Core\Test\TestDefaults;

/**
 * @internal
 */
#[Package('checkout')]
trait OrderFixture
{
    use BasicTestDataBehaviour;
    use EntityFixturesBase;

    /**
     * @throws \JsonException
     *
     * @return list<array<string, mixed>>
     */
    private function getOrderData(string $orderId, Context $context): array
    {
        $orderCustomerId = Uuid::randomHex();
        $addressId = Uuid::randomHex();
        $orderLineItemId = Uuid::randomHex();
        $countryStateId = Uuid::randomHex();
        $customerId = Uuid::randomHex();
        $orderNumber = Uuid::randomHex();
        $deliveryId = Uuid::randomHex();

        /** @var EntityRepository<ChannelCollection> $channelRepository */
        $channelRepository = static::getContainer()->get('channel.repository');

        $channel = $channelRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('id', TestDefaults::CHANNEL)),
            $context
        )->getEntities()->first();
        static::assertInstanceOf(ChannelEntity::class, $channel);

        $paymentMethodId = $channel->getPaymentMethodId();
        $shippingMethodId = $channel->getShippingMethodId();
        $salutationId = $this->getValidSalutationId();
        $countryId = $this->getValidCountryId(TestDefaults::CHANNEL);

        $order = [
            [
                'id' => $orderId,
                'orderNumber' => $orderNumber,
                'price' => new CartPrice(10, 10, 10, new CalculatedTaxCollection(), new TaxRuleCollection(), CartPrice::TAX_STATE_NET),
                'shippingCosts' => new CalculatedPrice(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()),
                'stateId' => static::getContainer()->get(InitialStateIdLoader::class)->get(OrderStates::STATE_MACHINE),
                'versionId' => Defaults::LIVE_VERSION,
                'paymentMethodId' => $paymentMethodId,
                'currencyId' => Defaults::CURRENCY,
                'currencyFactor' => 1,
                'channelId' => TestDefaults::CHANNEL,
                'orderDateTime' => '2019-04-01 08:36:43.267',
                'itemRounding' => json_decode(json_encode(new CashRoundingConfig(2, 0.01, true), \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR),
                'totalRounding' => json_decode(json_encode(new CashRoundingConfig(2, 0.01, true), \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR),
                'deliveries' => [
                    [
                        'id' => $deliveryId,
                        'stateId' => static::getContainer()->get(InitialStateIdLoader::class)->get(OrderDeliveryStates::STATE_MACHINE),
                        'shippingMethodId' => $shippingMethodId,
                        'shippingCosts' => new CalculatedPrice(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()),
                        'shippingDateEarliest' => (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_FORMAT),
                        'shippingDateLatest' => (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_FORMAT),
                        'shippingOrderAddress' => [
                            'salutationId' => $salutationId,
                            'firstName' => 'Floy',
                            'lastName' => 'Glover',
                            'zipcode' => '59438-0403',
                            'city' => 'Stellaberg',
                            'street' => 'street',
                            'country' => [
                                'name' => 'kasachstan',
                                'id' => $countryId,
                            ],
                        ],
                        'trackingCodes' => [
                            'CODE-1',
                            'CODE-2',
                        ],
                        'positions' => [
                            [
                                'price' => new CalculatedPrice(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()),
                                'orderLineItemId' => $orderLineItemId,
                            ],
                        ],
                    ],
                ],
                'lineItems' => [
                    [
                        'id' => $orderLineItemId,
                        'identifier' => 'test',
                        'quantity' => 1,
                        'type' => 'test',
                        'label' => 'test',
                        'price' => new CalculatedPrice(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()),
                        'priceDefinition' => new QuantityPriceDefinition(10, new TaxRuleCollection()),
                        'priority' => 100,
                        'good' => true,
                    ],
                ],
                'deepLinkCode' => 'BwvdEInxOHBbwfRw6oHF1Q_orfYeo9RY',
                'orderCustomerId' => $orderCustomerId,
                'orderCustomer' => [
                    'id' => $orderCustomerId,
                    'email' => 'test@example.com',
                    'firstName' => 'Noe',
                    'lastName' => 'Hill',
                    'salutationId' => $salutationId,
                    'title' => 'Doc',
                    'customerNumber' => 'Test',
                    'orderVersionId' => Defaults::LIVE_VERSION,
                    'customer' => [
                        'id' => $customerId,
                        'email' => 'test@example.com',
                        'firstName' => 'Noe',
                        'lastName' => 'Hill',
                        'salutationId' => $salutationId,
                        'title' => 'Doc',
                        'customerNumber' => 'Test',
                        'guest' => true,
                        'group' => ['name' => 'testse2323'],
                        'channelId' => TestDefaults::CHANNEL,
                        'defaultBillingAddressId' => $addressId,
                        'defaultShippingAddressId' => $addressId,
                        'addresses' => [
                            [
                                'id' => $addressId,
                                'salutationId' => $salutationId,
                                'firstName' => 'Floy',
                                'lastName' => 'Glover',
                                'zipcode' => '59438-0403',
                                'city' => 'Stellaberg',
                                'street' => 'street',
                                'countryStateId' => $countryStateId,
                                'country' => [
                                    'name' => 'kasachstan',
                                    'id' => $countryId,
                                    'states' => [
                                        [
                                            'id' => $countryStateId,
                                            'name' => 'oklahoma',
                                            'shortCode' => 'OH',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'billingAddressId' => $addressId,
                'addresses' => [
                    [
                        'salutationId' => $salutationId,
                        'firstName' => 'Floy',
                        'lastName' => 'Glover',
                        'zipcode' => '59438-0403',
                        'city' => 'Stellaberg',
                        'street' => 'street',
                        'countryId' => $countryId,
                        'id' => $addressId,
                    ],
                ],
            ],
        ];

        return $order;
    }
}
