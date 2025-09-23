<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Rule\AlwaysValidRule;
use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\ChannelApiTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Util\Random;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\ChannelContextPersister;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\Test\Integration\Traits\Promotion\PromotionTestFixtureBehaviour;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @internal
 */
#[Package('checkout')]
#[Group('front-api')]
#[Group('cart')]
class CartItemAddRouteTest extends TestCase
{
    use ChannelApiTestBehaviour;
    use IntegrationTestBehaviour;
    use PromotionTestFixtureBehaviour;

    private KernelBrowser $browser;

    private IdsCollection $ids;

    /**
     * @var EntityRepository<ProductCollection>
     */
    private EntityRepository $productRepository;

    protected function setUp(): void
    {
        $this->ids = new IdsCollection();

        $this->browser = $this->createCustomChannelBrowser([
            'id' => $this->ids->create('channel'),
        ]);

        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $this->ids->create('token'));
        $this->productRepository = static::getContainer()->get('product.repository');

        $this->createTestData();
    }

    public function testFillCartProducts(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/checkout/cart/line-item',
                [
                    'items' => [
                        [
                            'id' => $this->ids->get('p1'),
                            'label' => 'foo',
                            'type' => 'product',
                            'referencedId' => $this->ids->get('p1'),
                        ],
                    ],
                ]
            );

        static::assertSame(200, $this->browser->getResponse()->getStatusCode());

        $response = json_decode($this->browser->getResponse()->getContent() ?: '', true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame('cart', $response['apiAlias']);
        static::assertSame(10, $response['price']['totalPrice']);
        static::assertCount(1, $response['lineItems']);
        static::assertSame('Test', $response['lineItems'][0]['label']);
    }

    public function testAddExistingLineItemChangesQuantityAndMarksModified(): void
    {
        $this->browser
            ->jsonRequest(
                'POST',
                '/front-api/checkout/cart/line-item',
                [
                    'items' => [
                        [
                            'id' => $this->ids->get('p2'),
                            'label' => 'foo',
                            'type' => 'product',
                            'referencedId' => $this->ids->get('p2'),
                            'quantity' => 9,
                        ],
                    ],
                ]
            );
        static::assertSame(200, $this->browser->getResponse()->getStatusCode());

        $this->browser
            ->jsonRequest(
                'POST',
                '/front-api/checkout/cart/line-item',
                [
                    'items' => [
                        [
                            'id' => $this->ids->get('p2'),
                            'label' => 'foo',
                            'type' => 'product',
                            'referencedId' => $this->ids->get('p2'),
                            'quantity' => 1,
                        ],
                    ],
                ]
            );
        static::assertSame(200, $this->browser->getResponse()->getStatusCode());

        $response = json_decode($this->browser->getResponse()->getContent() ?: '', true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame('cart', $response['apiAlias']);
        static::assertSame(50, $response['price']['totalPrice']);
        static::assertCount(1, $response['lineItems']);
        static::assertSame('Test', $response['lineItems'][0]['label']);
    }

    public function testFillCartOutOfStock(): void
    {
        $this->browser->setServerParameter('HTTP_sw-include-seo-urls', '1');
        $this->browser
            ->request(
                'POST',
                '/front-api/checkout/cart/line-item',
                [
                    'items' => [
                        [
                            'id' => $this->ids->get('p3'),
                            'label' => 'foo',
                            'type' => 'product',
                            'referencedId' => $this->ids->get('p3'),
                        ],
                    ],
                ]
            );

        static::assertSame(200, $this->browser->getResponse()->getStatusCode());

        $response = json_decode($this->browser->getResponse()->getContent() ?: '', true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame('cart', $response['apiAlias']);
        static::assertSame(0, $response['price']['totalPrice']);
        static::assertCount(0, $response['lineItems']);
        static::assertCount(1, $response['errors']);
        static::assertSame('The product Test is no longer available', array_column($response['errors'], 'message')[0]);
    }

    public function testFillCartMultipleProducts(): void
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
                        [
                            'id' => $this->ids->get('p2'),
                            'type' => 'product',
                            'referencedId' => $this->ids->get('p2'),
                        ],
                    ],
                ]
            );

        static::assertSame(200, $this->browser->getResponse()->getStatusCode());

        $response = json_decode($this->browser->getResponse()->getContent() ?: '', true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame('cart', $response['apiAlias']);
        static::assertSame(20, $response['price']['totalPrice']);
        static::assertCount(2, $response['lineItems']);
        static::assertSame('Test', $response['lineItems'][0]['label']);
    }

    public function testAddCustomWithoutPermission(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/checkout/cart/line-item',
                [
                    'items' => [
                        [
                            'label' => 'Test',
                            'type' => 'credit',
                            'priceDefinition' => [
                                'price' => 100.0,
                                'type' => 'absolute',
                                'absolute' => 1.0,
                            ],
                        ],
                    ],
                ]
            );

        static::assertSame(403, $this->browser->getResponse()->getStatusCode());

        $response = json_decode($this->browser->getResponse()->getContent() ?: '', true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame('CHECKOUT__INSUFFICIENT_PERMISSION', $response['errors'][0]['code']);
    }

    public function testAddCustomWithPermission(): void
    {
        $this->enableAdminAccess();
        $this->browser
            ->request(
                'POST',
                '/front-api/checkout/cart/line-item',
                [
                    'items' => [
                        [
                            'label' => 'Test',
                            'type' => 'credit',
                            'priceDefinition' => [
                                'price' => 100.0,
                                'type' => 'absolute',
                                'absolute' => 1.0,
                            ],
                        ],
                    ],
                ]
            );

        static::assertSame(200, $this->browser->getResponse()->getStatusCode());

        $response = json_decode($this->browser->getResponse()->getContent() ?: '', true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame(100, $response['price']['totalPrice']);
    }

    public function testCustomTaxIncludedInShippingCostTaxes(): void
    {
        $this->enableAdminAccess();

        $this->browser
            ->request(
                'POST',
                '/front-api/checkout/cart/line-item',
                [
                    'items' => [
                        [
                            'id' => $this->ids->get('p1'),
                            'label' => 'product item',
                            'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
                            'priceDefinition' => [
                                'price' => 100,
                                'type' => 'quantity',
                            ],
                            'referencedId' => $this->ids->get('p1'),
                        ],
                        [
                            'label' => 'custom item',
                            'type' => LineItem::CUSTOM_LINE_ITEM_TYPE,
                            'tax' => [
                                'id' => Uuid::randomHex(),
                                'name' => 'taxCustomItem',
                            ],
                            'priceDefinition' => [
                                'price' => 150,
                                'type' => 'quantity',
                            ],
                        ],
                    ],
                ]
            );

        static::assertSame(200, $this->browser->getResponse()->getStatusCode());
    }

    public function testAddPromotion(): void
    {
        $promotionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $code = 'BF' . Random::getAlphanumericString(5);

        $context = static::getContainer()->get(ChannelContextFactory::class)->create(Uuid::randomHex(), $this->ids->get('channel'));

        $this->createTestFixtureProduct($productId, 800, static::getContainer(), $context);

        $this->createPromotion(
            $promotionId,
            $code,
            static::getContainer()->get('promotion.repository'),
            $context
        );

        $this->createTestFixtureDiscount($promotionId, PromotionDiscountEntity::TYPE_ABSOLUTE, PromotionDiscountEntity::SCOPE_CART, 10, null, static::getContainer(), $context);

        // Add product
        $this->browser
            ->request(
                'POST',
                '/front-api/checkout/cart/line-item',
                [
                    'items' => [
                        [
                            'id' => $productId,
                            'type' => 'product',
                            'referencedId' => $productId,
                        ],
                    ],
                ]
            );

        static::assertSame(200, $this->browser->getResponse()->getStatusCode());

        // Add code
        $this->browser
            ->request(
                'POST',
                '/front-api/checkout/cart/line-item',
                [
                    'items' => [
                        [
                            'type' => 'promotion',
                            'referencedId' => $code,
                        ],
                    ],
                ]
            );

        static::assertSame(200, $this->browser->getResponse()->getStatusCode());

        $response = json_decode($this->browser->getResponse()->getContent() ?: '', true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame('cart', $response['apiAlias']);
        static::assertSame(790, $response['price']['totalPrice']);
        static::assertCount(2, $response['lineItems']);
        static::assertSame('Test', $response['lineItems'][0]['label']);
    }

    private function createTestData(): void
    {
        $rule = Uuid::randomHex();
        static::getContainer()->get('rule.repository')->create([
            ['id' => $rule, 'name' => 'test', 'priority' => 1, 'conditions' => [['type' => (new AlwaysValidRule())->getName()]]],
        ], Context::createDefaultContext());

        $this->productRepository->create([
            [
                'id' => $this->ids->create('p1'),
                'productNumber' => $this->ids->get('p1'),
                'stock' => 10,
                'name' => 'Test',
                'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 10, 'net' => 9, 'linked' => false]],
                'manufacturer' => ['id' => $this->ids->create('manufacturerId'), 'name' => 'test'],
                'tax' => ['id' => $this->ids->create('tax'), 'taxRate' => 17, 'name' => 'with id'],
                'active' => true,
                'visibilities' => [
                    ['channelId' => $this->ids->get('channel'), 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
                ],
            ],
        ], Context::createDefaultContext());

        $this->productRepository->create([
            [
                'id' => $this->ids->create('p2'),
                'productNumber' => $this->ids->get('p2'),
                'stock' => 10,
                'name' => 'Test',
                'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 10, 'net' => 9, 'linked' => false]],
                'manufacturer' => ['id' => $this->ids->get('manufacturerId'), 'name' => 'test'],
                'tax' => ['id' => $this->ids->get('tax'), 'taxRate' => 17, 'name' => 'with id'],
                'active' => true,
                'visibilities' => [
                    ['channelId' => $this->ids->get('channel'), 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
                ],
                'prices' => [
                    [
                        'quantityStart' => 1,
                        'quantityEnd' => 9,
                        'ruleId' => $rule,
                        'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 10, 'net' => 8, 'linked' => false]],
                    ],
                    [
                        'quantityStart' => 10,
                        'ruleId' => $rule,
                        'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 5, 'net' => 4, 'linked' => false]],
                    ],
                ],
            ],
        ], Context::createDefaultContext());

        $this->productRepository->create([
            [
                'id' => $this->ids->create('p3'),
                'productNumber' => $this->ids->get('p3'),
                'stock' => 0,
                'name' => 'Test',
                'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 10, 'net' => 9, 'linked' => false]],
                'manufacturer' => ['id' => $this->ids->get('manufacturerId'), 'name' => 'test'],
                'tax' => ['id' => $this->ids->get('tax'), 'taxRate' => 17, 'name' => 'with id'],
                'active' => true,
                'isCloseout' => true,
                'visibilities' => [
                    ['channelId' => $this->ids->get('channel'), 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
                ],
            ],
        ], Context::createDefaultContext());
    }

    private function enableAdminAccess(): void
    {
        $token = $this->browser->getServerParameter('HTTP_SW_CONTEXT_TOKEN');
        $payload = static::getContainer()->get(ChannelContextPersister::class)->load($token, $this->ids->get('channel'));

        $payload[ChannelContextService::PERMISSIONS] = [CheckoutPermissions::ALLOW_PRODUCT_PRICE_OVERWRITES => true];

        static::getContainer()->get(ChannelContextPersister::class)->save($token, $payload, $this->ids->get('channel'));
    }
}
