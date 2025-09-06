<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Test\Flow;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\ChannelApiTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\CountryAddToChannelTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\CustomField\CustomFieldTypes;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestDefaults;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @internal
 */
#[Package('after-sales')]
trait OrderActionTrait
{
    use ChannelApiTestBehaviour;
    use CountryAddToChannelTestBehaviour;
    use IntegrationTestBehaviour;

    private KernelBrowser $browser;

    private IdsCollection $ids;

    /**
     * @var ?EntityRepository<CustomerCollection>
     */
    private ?EntityRepository $customerRepository = null;

    private function createCustomerAndLogin(): void
    {
        $email = Uuid::randomHex() . '@example.com';
        $this->prepareCustomer($email);

        $this->login($email, 'heyframe');
    }

    /**
     * @param array<string, mixed> $additionalData
     */
    private function prepareCustomer(?string $email = null, array $additionalData = []): void
    {
        static::assertNotNull($this->customerRepository);

        $customer = array_merge([
            'id' => $this->ids->create('customer'),
            'channelId' => $this->ids->get('channel'),
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'email' => $email,
            'password' => TestDefaults::HASHED_PASSWORD,
            'nickname' => 'Mustermann',
            'customerNumber' => '12345',
        ], $additionalData);

        $this->customerRepository->create([$customer], Context::createDefaultContext());
    }

    private function login(?string $email = null, ?string $password = null): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/account/login',
                [
                    'email' => $email,
                    'password' => $password,
                ]
            );

        $response = $this->browser->getResponse();

        // After login successfully, the context token will be set in the header
        $contextToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
        static::assertNotEmpty($contextToken);

        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $contextToken);
    }

    private function prepareProductTest(): void
    {
        static::getContainer()->get('product.repository')->create([
            [
                'id' => $this->ids->create('p1'),
                'productNumber' => $this->ids->get('p1'),
                'stock' => 10,
                'name' => 'Test',
                'productType' => 'Test',
                'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 10, 'net' => 9, 'linked' => false]],
                'active' => true,
                'visibilities' => [
                    ['channelId' => $this->ids->get('channel'), 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
                ],
            ],
        ], Context::createDefaultContext());
    }

    private function submitOrder(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/checkout/cart/line-item',
                [
                    'items' => [
                        [
                            'id' => $this->ids->get('p1'),
                            'type' => 'product',
                            'referencedId' => $this->ids->get('p1'),
                        ],
                    ],
                ]
            );

        $this->browser
            ->request(
                'POST',
                '/front-api/checkout/order',
                [
                    'affiliateCode' => 'test affiliate code',
                ]
            );
    }

    private function cancelOrder(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/order/state/cancel',
                [
                    'orderId' => $this->ids->get('order'),
                ]
            );
    }

    /**
     * @param array<string, mixed> $additionalData
     */
    private function createOrder(string $customerId, array $additionalData = []): void
    {
        static::getContainer()->get('order.repository')->create([
            array_merge([
                'id' => $this->ids->create('order'),
                'itemRounding' => json_decode(json_encode(new CashRoundingConfig(2, 0.01, true), \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR),
                'totalRounding' => json_decode(json_encode(new CashRoundingConfig(2, 0.01, true), \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR),
                'orderDateTime' => (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                'price' => new CartPrice(10, 10, 10),
                'orderCustomer' => [
                    'customerId' => $customerId,
                    'email' => 'test@example.com',
                    'name' => 'Max',
                    'nickname' => 'Mustermann',
                ],
                'orderNumber' => Uuid::randomHex(),
                'stateId' => $this->getStateMachineState(),
                'paymentMethodId' => $this->getValidPaymentMethodId(),
                'currencyId' => Defaults::CURRENCY,
                'currencyFactor' => 1.0,
                'channelId' => TestDefaults::CHANNEL,
                'lineItems' => [
                    [
                        'id' => $this->ids->create('line-item'),
                        'identifier' => $this->ids->create('line-item'),
                        'quantity' => 1,
                        'label' => 'label',
                        'type' => LineItem::CUSTOM_LINE_ITEM_TYPE,
                        'price' => new CalculatedPrice(200, 200),
                        'priceDefinition' => new QuantityPriceDefinition(200),
                    ],
                ],
                'context' => '{}',
                'payload' => '{}',
            ], $additionalData),
        ], Context::createDefaultContext());
    }

    private function getStateId(string $state, string $machine): string
    {
        return static::getContainer()->get(Connection::class)
            ->fetchOne('
                SELECT LOWER(HEX(state_machine_state.id))
                FROM state_machine_state
                    INNER JOIN  state_machine
                    ON state_machine.id = state_machine_state.state_machine_id
                    AND state_machine.technical_name = :machine
                WHERE state_machine_state.technical_name = :state
            ', [
                'state' => $state,
                'machine' => $machine,
            ]) ?: '';
    }

    private function createCustomField(string $name, string $entity, string $type = CustomFieldTypes::SELECT): string
    {
        $customFieldId = Uuid::randomHex();
        $customFieldSetId = Uuid::randomHex();
        $data = [
            'id' => $customFieldId,
            'name' => $name,
            'type' => $type,
            'customFieldSetId' => $customFieldSetId,
            'config' => [
                'componentName' => 'sw-field',
                'customFieldPosition' => 1,
                'customFieldType' => $type,
                'type' => $type,
                'label' => [
                    'en-GB' => 'lorem_ipsum',
                    'zh-CN' => 'lorem_ipsum',
                ],
            ],
            'customFieldSet' => [
                'id' => $customFieldSetId,
                'name' => 'Custom_Field_Set',
                'relations' => [[
                    'id' => Uuid::randomHex(),
                    'customFieldSetId' => $customFieldSetId,
                    'entityName' => $entity,
                ]],
            ],
        ];

        static::getContainer()->get('custom_field.repository')
            ->create([$data], Context::createDefaultContext());

        return $customFieldId;
    }
}
