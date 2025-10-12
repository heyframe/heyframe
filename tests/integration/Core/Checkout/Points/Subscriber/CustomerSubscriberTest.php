<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Points\Subscriber;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Points\PointsEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\ChannelApiTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerSubscriberTest extends TestCase
{
    use ChannelApiTestBehaviour;
    use IntegrationTestBehaviour;

    private IdsCollection $ids;

    protected function setUp(): void
    {
        $this->ids = new IdsCollection();

        $this->browser = $this->createCustomChannelBrowser([
            'id' => $this->ids->create('channel'),
        ]);
        $this->assignChannelContext($this->browser);
    }

    public function testCustomerLoaded(): void
    {
        $customerId = $this->createCustomer();

        /** @var CustomerEntity $customer */
        $customer = static::getContainer()
            ->get('customer.repository')
            ->search((new Criteria([$customerId]))->addAssociation('points'), Context::createDefaultContext())
            ->getEntities()->first();
        static::assertNotNull($customer->getPoints());
    }

    public function testCustomerWritten(): void
    {
        $customerId = $this->createCustomer();

        $points = static::getContainer()
            ->get('points.repository')
            ->search((new Criteria())->addFilter(
                new EqualsFilter('customerId', $customerId)
            ), Context::createDefaultContext())
            ->getEntities()->first();

        static::assertInstanceOf(PointsEntity::class, $points);
        static::assertSame(0.0, $points->getBalance());
    }
}
