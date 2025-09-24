<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Customer\Subscriber;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Api\Context\SystemSource;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Test\Integration\Traits\CustomerTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerSubscriberTest extends TestCase
{
    use CustomerTestTrait;

    public function testCustomerWritten(): void
    {
        $customerId = $this->createCustomer();
        /** @var CustomerEntity $customer */
        $customer = static::getContainer()
            ->get('customer.repository')
            ->search((new Criteria([$customerId]))->addAssociation('roles'), new Context(new SystemSource()))
            ->getEntities()->first();
        static::assertCount(1, $customer->getRoles());
    }
}
