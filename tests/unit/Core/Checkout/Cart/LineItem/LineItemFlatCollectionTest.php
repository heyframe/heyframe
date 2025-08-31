<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemFlatCollection;
use HeyFrame\Core\Framework\Log\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(LineItemFlatCollection::class)]
class LineItemFlatCollectionTest extends TestCase
{
    /**
     * This test verifies that its possible
     * to add a line item with a specific id multiple times.
     * It must not be aggregated within an associative array in the flat list.
     *
     * @throws CartException
     */
    public function testCanAddSameItemMultipleTimes(): void
    {
        $lineItem = new LineItem('ABC', '');
        $lineItem->setStackable(true);

        $collection = new LineItemFlatCollection();

        $collection->add($lineItem);
        $collection->add($lineItem);

        static::assertCount(2, $collection);
    }
}
