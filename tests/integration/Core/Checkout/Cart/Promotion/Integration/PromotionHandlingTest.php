<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Cart\Promotion\Integration;

use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
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
class PromotionHandlingTest extends TestCase
{
    use IntegrationTestBehaviour;
    use PromotionIntegrationTestBehaviour;
    use PromotionTestFixtureBehaviour;

    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartService = static::getContainer()->get(CartService::class);

        $this->context = static::getContainer()->get(ChannelContextFactory::class)->create(Uuid::randomHex(), TestDefaults::CHANNEL);
    }

    /**
     * This test verifies that our promotions are not added
     * if our cart is empty and has no products yet.
     */
    #[Group('promotions')]
    public function testPromotionNotAddedWithoutProduct(): void
    {
        $productId = Uuid::randomHex();
        $code = 'BF19';

        $this->createTestFixtureProduct($productId, 119, static::getContainer(), $this->context);
        $this->createTestFixturePercentagePromotion(Uuid::randomHex(), $code, 100, null, static::getContainer());

        $cart = $this->cartService->getCart($this->context->getToken(), $this->context);

        // add our promotion to our cart
        $cart = $this->addPromotionCode($code, $cart, $this->cartService, $this->context);

        static::assertCount(0, $cart->getLineItems());
    }

    /**
     * This test verifies that our promotions are correctly
     * removed when also removing the last product
     */
    #[Group('promotions')]
    public function testPromotionsRemovedWithProduct(): void
    {
        $productId = Uuid::randomHex();
        $code = 'BF19';

        $this->createTestFixtureProduct($productId, 119, static::getContainer(), $this->context);
        $this->createTestFixturePercentagePromotion(Uuid::randomHex(), $code, 100, null, static::getContainer());

        $cart = $this->cartService->getCart($this->context->getToken(), $this->context);

        $cart = $this->addProduct($productId, 1, $cart, $this->cartService, $this->context);

        // add our promotion to our cart
        $cart = $this->addPromotionCode($code, $cart, $this->cartService, $this->context);

        $ids = array_keys($cart->getLineItems()->getElements());
        static::assertArrayHasKey(0, $ids);

        // remove our first item (product)
        $cart = $this->cartService->remove($cart, $ids[0], $this->context);

        static::assertCount(0, $cart->getLineItems());
    }
}
