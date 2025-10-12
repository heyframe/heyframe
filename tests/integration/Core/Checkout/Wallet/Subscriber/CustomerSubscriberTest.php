<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Wallet\Subscriber;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Wallet\WalletEntity;
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
            ->search((new Criteria([$customerId]))->addAssociation('wallet'), Context::createDefaultContext())
            ->getEntities()->first();
        static::assertNotNull($customer->getWallet());
    }

    public function testCustomerWritten(): void
    {
        $customerId = $this->createCustomer();

        $wallet = static::getContainer()
            ->get('wallet.repository')
            ->search((new Criteria())->addFilter(
                new EqualsFilter('customerId', $customerId)
            ), Context::createDefaultContext())
            ->getEntities()->first();

        static::assertInstanceOf(WalletEntity::class, $wallet);
        static::assertSame(0.0, $wallet->getBalance());
    }
}
