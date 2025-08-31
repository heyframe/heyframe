<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem\Group;

use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupServiceRegistry;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\Packager\LineItemGroupCountPackager;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\Sorter\LineItemGroupPriceAscSorter;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\Sorter\LineItemGroupPriceDescSorter;
use HeyFrame\Core\Framework\Log\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(LineItemGroupServiceRegistry::class)]
class LineItemGroupServiceRegistryTest extends TestCase
{
    /**
     * This test verifies that our packagers are
     * correctly registered in our registry.
     */
    public function testPackagersAreRegistered(): void
    {
        $packagers = [
            new LineItemGroupCountPackager(),
        ];
        $sorters = [];

        $registry = new LineItemGroupServiceRegistry($packagers, $sorters);

        $generator = iterator_to_array($registry->getPackagers());
        static::assertCount(1, $generator);
    }

    /**
     * This test verifies that our sorters are
     * correctly registered in our registry.
     */
    public function testSortersAreRegistered(): void
    {
        $packagers = [];
        $sorters = [
            new LineItemGroupPriceAscSorter(),
        ];

        $registry = new LineItemGroupServiceRegistry($packagers, $sorters);

        $generator = iterator_to_array($registry->getSorters());
        static::assertCount(1, $generator);
    }

    /**
     * This test verifies that we can retrieve
     * our packager by its key.
     */
    #[Group('lineitemgroup')]
    public function testGetPackagerByKey(): void
    {
        $packager = new LineItemGroupCountPackager();

        $packagers = [
            $packager,
        ];
        $sorters = [];

        $registry = new LineItemGroupServiceRegistry($packagers, $sorters);

        static::assertSame($packager, $registry->getPackager($packager->getKey()));
    }

    /**
     * This test verifies that we can retrieve
     * our sorter by its key.
     */
    #[Group('lineitemgroup')]
    public function testGetSorterByKey(): void
    {
        $sorter = new LineItemGroupPriceAscSorter();

        $packagers = [];
        $sorters = [
            $sorter,
            new LineItemGroupPriceDescSorter(),
        ];

        $registry = new LineItemGroupServiceRegistry($packagers, $sorters);

        static::assertSame($sorter, $registry->getSorter($sorter->getKey()));
    }
}
