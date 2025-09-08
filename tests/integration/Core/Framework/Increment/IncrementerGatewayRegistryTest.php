<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\Increment;

use HeyFrame\Core\Framework\Increment\AbstractIncrementer;
use HeyFrame\Core\Framework\Increment\Exception\IncrementGatewayNotFoundException;
use HeyFrame\Core\Framework\Increment\IncrementGatewayRegistry;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class IncrementerGatewayRegistryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGet(): void
    {
        $registry = static::getContainer()->get('heyframe.increment.gateway.registry');

        static::assertInstanceOf(AbstractIncrementer::class, $registry->get(IncrementGatewayRegistry::USER_ACTIVITY_POOL));
        static::assertInstanceOf(AbstractIncrementer::class, $registry->get(IncrementGatewayRegistry::MESSAGE_QUEUE_POOL));
    }

    public function testGetWithInvalidPool(): void
    {
        static::expectException(IncrementGatewayNotFoundException::class);
        static::expectExceptionMessage('Increment gateway for pool "custom_pool" was not found.');

        $registry = static::getContainer()->get('heyframe.increment.gateway.registry');
        static::assertNull($registry->get('custom_pool'));
    }
}
