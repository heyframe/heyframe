<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Cart\Error\IncompleteLineItemError;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemValidator;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(LineItemValidator::class)]
class LineItemValidatorTest extends TestCase
{
    public function testValidateEmptyCart(): void
    {
        $cart = new Cart('test');

        $validator = new LineItemValidator();
        $errors = new ErrorCollection();
        $validator->validate($cart, $errors, $this->createMock(ChannelContext::class));

        static::assertCount(0, $errors);
    }

    public function testValidateWithValidLineItem(): void
    {
        $cart = new Cart('test');
        $lineItem = new LineItem('id', 'fake');
        $lineItem->setLabel('Label');
        $lineItem->setPrice(new CalculatedPrice(5, 10));

        $cart->add($lineItem);

        $validator = new LineItemValidator();
        $errors = new ErrorCollection();
        $validator->validate($cart, $errors, $this->createMock(ChannelContext::class));

        static::assertCount(0, $errors);
    }

    public function testValidateWithoutLabel(): void
    {
        $cart = new Cart('test');
        $lineItem = new LineItem('id', 'fake');
        $lineItem->setPrice(new CalculatedPrice(5, 10));
        $cart->add($lineItem);

        $validator = new LineItemValidator();
        $errors = new ErrorCollection();
        $validator->validate($cart, $errors, $this->createMock(ChannelContext::class));

        static::assertCount(1, $errors);
        static::assertInstanceOf(IncompleteLineItemError::class, $errors->first());
        static::assertSame('id', $errors->first()->getId());
        static::assertSame('label', $errors->first()->getMessageKey());
    }

    public function testValidateWithoutLabelGotRemoved(): void
    {
        $cart = new Cart('test');
        $lineItem = new LineItem('id', 'fake');
        $lineItem->setPrice(new CalculatedPrice(5, 10));
        $cart->add($lineItem);

        $validator = new LineItemValidator();
        $errors = new ErrorCollection();
        $validator->validate($cart, $errors, $this->createMock(ChannelContext::class));

        static::assertCount(0, $cart->getLineItems());
    }

    public function testValidateWithoutPrice(): void
    {
        $cart = new Cart('test');
        $lineItem = new LineItem('id', 'fake');
        $lineItem->setLabel('Label');
        $cart->add($lineItem);

        $validator = new LineItemValidator();
        $errors = new ErrorCollection();
        $validator->validate($cart, $errors, $this->createMock(ChannelContext::class));

        static::assertCount(1, $errors);
        static::assertInstanceOf(IncompleteLineItemError::class, $errors->first());
        static::assertSame('id', $errors->first()->getId());
        static::assertSame('price', $errors->first()->getMessageKey());
    }

    public function testValidateWithoutLabelAndPrice(): void
    {
        $cart = new Cart('test');
        $lineItem = new LineItem('id', 'fake');
        $cart->add($lineItem);

        $validator = new LineItemValidator();
        $errors = new ErrorCollection();
        $validator->validate($cart, $errors, $this->createMock(ChannelContext::class));

        static::assertCount(1, $errors);
        static::assertSame('id', $errors->first()?->getId());
        static::assertSame('price', $errors->last()?->getMessageKey());
    }
}
