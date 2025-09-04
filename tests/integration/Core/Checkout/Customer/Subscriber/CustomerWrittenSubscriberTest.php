<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Customer\Subscriber;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Wallet\WalletEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Points\PointsEntity;
use HeyFrame\Core\Test\Integration\Traits\CustomerTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerWrittenSubscriberTest extends TestCase
{
    use CustomerTestTrait;

    public function testCustomerWritten(): void
    {
        $customerId = $this->createCustomer();

        $wallet = static::getContainer()
            ->get('wallet.repository')
            ->search((new Criteria())->addFilter(
                new EqualsFilter('identifier', CustomerDefinition::ENTITY_NAME),
                new EqualsFilter('referencedId', $customerId)
            ), Context::createDefaultContext())
            ->getEntities()->first();
        static::assertInstanceOf(WalletEntity::class, $wallet);
        static::assertSame(0.0, $wallet->getBalance());

        $points = static::getContainer()
            ->get('points.repository')
            ->search((new Criteria())->addFilter(
                new EqualsFilter('identifier', CustomerDefinition::ENTITY_NAME),
                new EqualsFilter('referencedId', $customerId)
            ), Context::createDefaultContext())
            ->getEntities()->first();
        static::assertInstanceOf(PointsEntity::class, $points);
        static::assertSame(0, $points->getTotalPoints());
    }
}
