<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Integration\Traits\Promotion;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Checkout\Cart\LineItemFactoryHandler\ProductLineItemFactory;
use HeyFrame\Core\Checkout\Promotion\Cart\PromotionItemBuilder;
use HeyFrame\Core\Checkout\Promotion\Cart\PromotionProcessor;
use HeyFrame\Core\Checkout\Promotion\Subscriber\Storefront\StorefrontCartSubscriber;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\Test\TestDefaults;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * @internal
 */
#[Package('checkout')]
trait PromotionIntegrationTestBehaviour
{
    private ChannelContext $context;

    /**
     * Gets a faked sales channel context
     * for the unit tests.
     */
    public function getContext(): ChannelContext
    {
        $this->context = static::getContainer()->get(ChannelContextFactory::class)->create(Uuid::randomHex(), TestDefaults::CHANNEL);

        return $this->context;
    }

    /**
     * Adds the provided product to the cart.
     *
     * @throws CartException
     */
    public function addProduct(string $productId, int $quantity, Cart $cart, CartService $cartService, ChannelContext $context): Cart
    {
        $factory = static::getContainer()->get(ProductLineItemFactory::class);
        $product = $factory->create(['id' => $productId, 'referencedId' => $productId, 'quantity' => $quantity], $context);

        return $cartService->add($cart, $product, $context);
    }

    /**
     * Adds the provided code to the current cart.
     */
    public function addPromotionCode(string $code, Cart $cart, CartService $cartService, ChannelContext $context): Cart
    {
        $itemBuilder = new PromotionItemBuilder();

        // ??? currencyPrecision is unused
        $lineItem = $itemBuilder->buildPlaceholderItem($code);

        $cart = $cartService->add($cart, $lineItem, $context);

        return $cart;
    }

    /**
     * Removes the provided code to the current cart.
     */
    public function removePromotionCode(string $code, Cart $cart, CartService $cartService, ChannelContext $context): Cart
    {
        $promotions = $cart->getLineItems()->filterType(PromotionProcessor::LINE_ITEM_TYPE);

        foreach ($promotions->getElements() as $promotion) {
            if ($promotion->getReferencedId() === $code) {
                return $cartService->remove($cart, $promotion->getId(), $context);
            }
        }

        return $cart;
    }

    /**
     * Gets all promotion codes that have been added
     * to the current session.
     *
     * @return array<mixed>
     */
    public function getSessionCodes(): array
    {
        $mockFileSessionStorage = static::getContainer()->get('session.storage.mock_file');
        static::assertInstanceOf(SessionStorageInterface::class, $mockFileSessionStorage);
        $session = new Session($mockFileSessionStorage);

        if (!$session->has(StorefrontCartSubscriber::SESSION_KEY_PROMOTION_CODES)) {
            return [];
        }

        return $session->get(StorefrontCartSubscriber::SESSION_KEY_PROMOTION_CODES);
    }
}
