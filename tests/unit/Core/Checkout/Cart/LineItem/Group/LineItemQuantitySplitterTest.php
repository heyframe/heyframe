<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem\Group;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemQuantitySplitter;
use HeyFrame\Core\Checkout\Cart\Price\CashRounding;
use HeyFrame\Core\Checkout\Cart\Price\GrossPriceCalculator;
use HeyFrame\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(LineItemQuantitySplitter::class)]
class LineItemQuantitySplitterTest extends TestCase
{
    private ChannelContext $salesChannelContext;

    protected function setUp(): void
    {
        $context = $this->createMock(ChannelContext::class);
        $context->method('getItemRounding')->willReturn(new CashRoundingConfig(2, 0.01, true));

        $this->salesChannelContext = $context;
    }

    public function testSplitTaxesUnrounded(): void
    {
        $splitter = $this->createQtySplitter();

        $lineItem = new LineItem(Uuid::randomHex(), LineItem::PRODUCT_LINE_ITEM_TYPE, Uuid::randomHex(), 10);
        $lineItem->setPrice(new CalculatedPrice(39.95, 399.50));
        $lineItem->setStackable(true);

        $newLineItem = $splitter->split($lineItem, 1, $this->salesChannelContext);

        static::assertNotSame($lineItem, $newLineItem);
        static::assertSame(1, $newLineItem->getQuantity());
        static::assertSame(39.95, $newLineItem->getPrice()?->getTotalPrice());
    }

    #[DataProvider('splitProvider')]
    public function testSplit(int $itemQty, int $splitterQty, bool $calcExpects): void
    {
        $splitter = $this->createQtySplitter();

        $lineItem = new LineItem(Uuid::randomHex(), LineItem::PRODUCT_LINE_ITEM_TYPE, Uuid::randomHex(), $itemQty);
        $lineItem->setPrice(new CalculatedPrice(10, 10 * $itemQty, $itemQty));
        $lineItem->setStackable(true);

        $newLineItem = $splitter->split($lineItem, $splitterQty, $this->salesChannelContext);

        if (!$calcExpects) {
            static::assertEquals($lineItem, $newLineItem);
        } else {
            $expectedPrice = 10.0 * $splitterQty;

            static::assertNotSame($lineItem, $newLineItem);
            static::assertSame($splitterQty, $newLineItem->getQuantity());
            static::assertSame($expectedPrice, $newLineItem->getPrice()?->getTotalPrice());
        }
    }

    /**
     * @return \Generator<string, array{0: int, 1: int, 2: bool}>
     */
    public static function splitProvider(): \Generator
    {
        yield 'should not split items when item qty = 10 and splitter qty = 10' => [10, 10, false];
        yield 'should split items when item qty = 10 and splitter qty = 9' => [10, 9, true];
        yield 'should split items when item qty = 9 and splitter qty = 10' => [9, 10, true];
    }

    private function createQtySplitter(): LineItemQuantitySplitter
    {
        $cashRounding = new CashRounding();

        $qtyCalc = new QuantityPriceCalculator(new GrossPriceCalculator($cashRounding));

        return new LineItemQuantitySplitter($qtyCalc);
    }
}
