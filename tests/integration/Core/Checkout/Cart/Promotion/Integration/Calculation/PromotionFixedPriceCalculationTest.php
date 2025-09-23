<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Cart\Promotion\Integration\Calculation;

use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use HeyFrame\Core\Checkout\Promotion\Cart\PromotionProcessor;
use HeyFrame\Core\Checkout\Promotion\PromotionCollection;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Util\Random;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\Test\Integration\Traits\Promotion\PromotionIntegrationTestBehaviour;
use HeyFrame\Core\Test\Integration\Traits\Promotion\PromotionTestFixtureBehaviour;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
class PromotionFixedPriceCalculationTest extends TestCase
{
    use IntegrationTestBehaviour;
    use PromotionIntegrationTestBehaviour;
    use PromotionTestFixtureBehaviour;

    /**
     * @var EntityRepository<ProductCollection>
     */
    protected EntityRepository $productRepository;

    protected CartService $cartService;

    /**
     * @var EntityRepository<PromotionCollection>
     */
    protected EntityRepository $promotionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = static::getContainer()->get(ChannelContextFactory::class)->create(Uuid::randomHex(), TestDefaults::CHANNEL);

        $this->productRepository = static::getContainer()->get('product.repository');
        $this->promotionRepository = static::getContainer()->get('promotion.repository');
        $this->cartService = static::getContainer()->get(CartService::class);
    }

    /**
     * This test verifies that our promotion components are really involved in our checkout.
     * We test that we always end with a final price of our promotion if that one is set to a "fixed price".
     * Our price would be 40 EUR. It must not matter how many items and products we have in there,
     * the final price should always be 40 EUR.
     */
    #[Group('promotions')]
    public function testFixedUnitDiscount(): void
    {
        $productId = Uuid::randomHex();
        $productIdTwo = Uuid::randomHex();
        $promotionId = Uuid::randomHex();
        $code = 'BF19';

        // add a new sample products
        $this->createTestFixtureProduct($productId, 100, static::getContainer(), $this->context);
        $this->createTestFixtureProduct($productIdTwo, 100, static::getContainer(), $this->context);

        // add a new promotion
        $this->createTestFixtureFixedDiscountPromotion($promotionId, 40, PromotionDiscountEntity::SCOPE_CART, $code, static::getContainer(), $this->context);

        $cart = $this->cartService->getCart($this->context->getToken(), $this->context);

        // add products to cart
        $cart = $this->addProduct($productId, 1, $cart, $this->cartService, $this->context);
        $cart = $this->addProduct($productIdTwo, 1, $cart, $this->cartService, $this->context);

        // add promotion to cart
        $cart = $this->addPromotionCode($code, $cart, $this->cartService, $this->context);

        static::assertSame(40.0, $cart->getPrice()->getPositionPrice());
        static::assertSame(40.0, $cart->getPrice()->getTotalPrice());
    }

    /**
     * if a automatic fixed price promotion (no code necessary) discount is removed
     * it should not be added again. This is a new feature - to block automatic promotions.
     */
    #[Group('promotions')]
    public function testRemoveOfFixedUnitPromotionsWithoutCode(): void
    {
        $productId = Uuid::randomHex();
        $promotionId = Uuid::randomHex();
        $context = static::getContainer()->get(ChannelContextFactory::class)->create(Uuid::randomHex(), TestDefaults::CHANNEL);

        // add a new sample product
        $this->createTestFixtureProduct($productId, 100, static::getContainer(), $context);

        // add a new promotion
        $this->createTestFixtureFixedDiscountPromotion($promotionId, 40, PromotionDiscountEntity::SCOPE_CART, null, static::getContainer(), $context);

        $cart = $this->cartService->getCart($context->getToken(), $context);

        // create first product and add to cart
        $cart = $this->addProduct($productId, 1, $cart, $this->cartService, $context);

        static::assertCount(2, $cart->getLineItems(), 'We expect two lineItems in cart');

        static::assertSame(40.0, $cart->getPrice()->getPositionPrice());
        static::assertSame(40.0, $cart->getPrice()->getTotalPrice());

        $discountLineItem = $cart->getLineItems()->filterType(PromotionProcessor::LINE_ITEM_TYPE)->first();
        static::assertNotNull($discountLineItem);
        $discountId = $discountLineItem->getId();

        // and try to remove promotion
        $cart = $this->cartService->remove($cart, $discountId, $context);

        static::assertCount(1, $cart->getLineItems(), 'We expect 1 lineItem in cart');

        static::assertSame(100.0, $cart->getPrice()->getPositionPrice());
        static::assertSame(100.0, $cart->getPrice()->getTotalPrice());
    }

    /**
     * This test verifies that we use the max value of our currency
     * instead of the defined fixed price, if existing.
     * Thus we create a promotion with a fixed price of 80
     * That would lead to 80 EUR for the product
     * But for your currency defined price, we use 65 as fixed price instead.
     * Our test needs to verify that we use 30 EUR, and end with a product sum of 65 EUR in the end.
     */
    #[Group('promotions')]
    public function testFixedUnitPriceDiscountWithCurrencyPrices(): void
    {
        $productId = Uuid::randomHex();
        $promotionId = Uuid::randomHex();
        $code = 'BF' . Random::getAlphanumericString(5);
        $context = static::getContainer()->get(ChannelContextFactory::class)->create(Uuid::randomHex(), TestDefaults::CHANNEL);

        $productGross = 100.0;
        $fixedPriceValue = 80;
        $currencyMaxValue = 65.0;

        $expectedPrice = $currencyMaxValue;

        // add a new sample product
        $this->createTestFixtureProduct($productId, $productGross, static::getContainer(), $context);

        $discountId = $this->createTestFixtureFixedDiscountPromotion($promotionId, $fixedPriceValue, PromotionDiscountEntity::SCOPE_CART, $code, static::getContainer(), $context);

        $this->createTestFixtureAdvancedPrice($discountId, Defaults::CURRENCY, $currencyMaxValue, static::getContainer());

        $cart = $this->cartService->getCart($context->getToken(), $context);

        // create product and add to cart
        $cart = $this->addProduct($productId, 1, $cart, $this->cartService, $context);

        static::assertSame($productGross, $cart->getPrice()->getTotalPrice());

        // create promotion and add to cart
        $cart = $this->addPromotionCode($code, $cart, $this->cartService, $context);

        static::assertSame($expectedPrice, $cart->getPrice()->getPositionPrice());
        static::assertSame($expectedPrice, $cart->getPrice()->getTotalPrice());
    }

    /**
     * This test verifies that we can assign a fixed total price for our promotion discount.
     * We test this by adding a scope of the "entire cart" which consists of 2 products with different quantities.
     * Then we add a promotion with a fixed price of 100 EUR.
     * This means that our final cart price should be 100 EUR and the discount need to be calculated correctly
     * by considering the existing cart items.
     */
    #[Group('promotions')]
    public function testFixedCartPriceDiscount(): void
    {
        $productId1 = Uuid::randomHex();
        $productId2 = Uuid::randomHex();
        $promotionId = Uuid::randomHex();
        $code = 'BF19';

        // add 2 test products
        $this->createTestFixtureProduct($productId1, 200, static::getContainer(), $this->context);
        $this->createTestFixtureProduct($productId2, 50, static::getContainer(), $this->context);

        // add a new promotion
        $this->createTestFixtureFixedDiscountPromotion($promotionId, 100, PromotionDiscountEntity::SCOPE_CART, $code, static::getContainer(), $this->context);

        $cart = $this->cartService->getCart($this->context->getToken(), $this->context);

        // create first product and add to cart
        $cart = $this->addProduct($productId1, 2, $cart, $this->cartService, $this->context);
        $cart = $this->addProduct($productId2, 3, $cart, $this->cartService, $this->context);

        // create promotion and add to cart
        $cart = $this->addPromotionCode($code, $cart, $this->cartService, $this->context);

        static::assertCount(3, $cart->getLineItems(), 'We expect 2 products and 1 discount in cart');

        static::assertSame(100.0, $cart->getPrice()->getPositionPrice());
        static::assertSame(100.0, $cart->getPrice()->getTotalPrice());
    }
}
