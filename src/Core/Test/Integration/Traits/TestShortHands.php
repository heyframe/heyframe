<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Integration\Traits;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItemFactoryHandler\ProductLineItemFactory;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use HeyFrame\Core\Content\Flow\Events\FlowSendMailActionEvent;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\Test\Integration\Builder\Customer\CustomerBuilder;
use HeyFrame\Core\Test\Integration\Helper\MailEventListener;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestDefaults;

/**
 * @codeCoverageIgnore
 *
 * @internal
 */
trait TestShortHands
{
    use KernelTestBehaviour;

    /**
     * @param array<string, mixed> $options
     */
    protected function getContext(?string $token = null, array $options = [], string $channelId = TestDefaults::CHANNEL): ChannelContext
    {
        $token ??= Uuid::randomHex();

        return static::getContainer()->get(ChannelContextFactory::class)
            ->create($token, $channelId, $options);
    }

    protected function addProductToCart(string $id, ChannelContext $context): Cart
    {
        $product = static::getContainer()->get(ProductLineItemFactory::class)
            ->create(['id' => $id, 'referencedId' => $id], $context);

        $cart = static::getContainer()->get(CartService::class)
            ->getCart($context->getToken(), $context);

        return static::getContainer()->get(CartService::class)
            ->add($cart, $product, $context);
    }

    protected function order(Cart $cart, ChannelContext $context, ?RequestDataBag $data = null): string
    {
        return static::getContainer()->get(CartService::class)
            ->order($cart, $context, $data ?? new RequestDataBag());
    }

    protected function assertProductInOrder(string $orderId, string $productId): OrderLineItemEntity
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);

        $criteria->addFilter(new AndFilter([
            new EqualsFilter('referencedId', $productId),
            new EqualsFilter('type', LineItem::PRODUCT_LINE_ITEM_TYPE),
            new EqualsFilter('orderId', $orderId),
        ]));

        $exists = static::getContainer()->get('order_line_item.repository')
            ->search($criteria, Context::createDefaultContext());

        static::assertCount(1, $exists);

        $item = $exists->first();

        static::assertInstanceOf(OrderLineItemEntity::class, $item);

        return $item;
    }

    protected function assertLineItemTotalPrice(Cart $cart, string $id, float $price): void
    {
        $item = $cart->get($id);

        static::assertInstanceOf(LineItem::class, $item, \sprintf('Can not find line item with id %s', $id));

        static::assertInstanceOf(CalculatedPrice::class, $item->getPrice(), \sprintf('Line item with id %s has no price', $id));

        static::assertSame($price, $item->getPrice()->getTotalPrice(), \sprintf('Line item with id %s has wrong total price', $id));
    }

    protected function assertLineItemUnitPrice(Cart $cart, string $id, float $price): void
    {
        $item = $cart->get($id);

        static::assertInstanceOf(LineItem::class, $item, \sprintf('Can not find line item with id %s', $id));

        static::assertInstanceOf(CalculatedPrice::class, $item->getPrice(), \sprintf('Line item with id %s has no price', $id));

        static::assertSame($price, $item->getPrice()->getUnitPrice(), \sprintf('Line item with id %s has wrong unit price', $id));
    }

    protected function assertLineItemInCart(Cart $cart, string $id): void
    {
        $item = $cart->get($id);

        static::assertInstanceOf(LineItem::class, $item, \sprintf('Can not find line item with id %s', $id));
    }

    protected function login(ChannelContext $context, ?string $customerId = null): ChannelContext
    {
        if ($customerId === null) {
            $customer = new CustomerBuilder(
                new IdsCollection(),
                Uuid::randomHex(),
                $context->getChannelId()
            );

            static::getContainer()->get('customer.repository')->create(
                [$customer->build()],
                Context::createDefaultContext()
            );

            $customerId = $customer->id;
        }

        return $this->getContext($context->getToken(), [
            ChannelContextService::CUSTOMER_ID => $customerId,
        ], $context->getChannelId());
    }

    protected function assertMailSent(MailEventListener $listener, string $type): void
    {
        static::assertTrue($listener->sent($type), \sprintf('Mail with type %s was not sent', $type));
    }

    /**
     * @return mixed
     */
    protected function mailListener(\Closure $closure)
    {
        $mapping = static::getContainer()->get(Connection::class)
            ->fetchAllKeyValue('SELECT LOWER(HEX(id)), technical_name FROM mail_template_type');

        $listener = new MailEventListener($mapping);

        $dispatcher = static::getContainer()->get('event_dispatcher');

        $dispatcher->addListener(FlowSendMailActionEvent::class, $listener);

        $result = $closure($listener);

        $dispatcher->removeListener(FlowSendMailActionEvent::class, $listener);

        return $result;
    }

    private function assertStock(string $productId, int $stock, int $available): void
    {
        $stocks = static::getContainer()->get(Connection::class)->fetchAssociative(
            'SELECT stock, available_stock FROM product WHERE id = :id',
            ['id' => Uuid::fromHexToBytes($productId)]
        );

        static::assertNotEmpty($stocks, \sprintf('Product with id %s not found', $productId));

        static::assertSame($stock, (int) $stocks['stock'], \sprintf('Product with id %s has wrong stock', $productId));

        static::assertSame($available, (int) $stocks['available_stock'], \sprintf('Product with id %s has wrong available stock', $productId));
    }
}
