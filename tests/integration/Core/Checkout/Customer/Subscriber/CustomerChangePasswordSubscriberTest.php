<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Customer\Subscriber;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\AdminFunctionalTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\ChannelApiTestBehaviour;
use HeyFrame\Core\Framework\Util\Hasher;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerChangePasswordSubscriberTest extends TestCase
{
    use AdminFunctionalTestBehaviour;
    use ChannelApiTestBehaviour;

    private KernelBrowser $browser;

    private IdsCollection $ids;

    /**
     * @var EntityRepository<CustomerCollection>
     */
    private EntityRepository $customerRepository;

    protected function setUp(): void
    {
        $this->ids = new IdsCollection();
        $this->browser = $this->createCustomChannelBrowser([
            'id' => $this->ids->create('channel'),
        ]);
        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $this->ids->create('token'));

        $this->customerRepository = static::getContainer()->get('customer.repository');
    }

    public function testClearLegacyWhenUserChangePassword(): void
    {
        $email = Uuid::randomHex() . '@heyframe.com';
        $password = 'ThisIsNewPassword';

        $newPassword = Uuid::randomHex();
        $customerId = $this->createCustomerWithLegacyPassword($email, $password);

        $context = Context::createDefaultContext();

        $this->getBrowser()->jsonRequest(
            'PATCH',
            '/api/customer/' . $customerId,
            ['password' => $newPassword]
        );

        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $customerId));

        /** @var CustomerEntity $customer */
        $customer = $this->customerRepository->search($criteria, $context)->first();

        static::assertNotNull($customer->getPassword());
        static::assertNull($customer->getLegacyPassword());
        static::assertNull($customer->getLegacyEncoder());

        $this->loginUser($email, $newPassword);
    }

    public function testNotClearLegacyDataWhenUserNotChangedPassword(): void
    {
        $email = Uuid::randomHex() . '@heyframe.com';
        $password = 'ThisIsNewPassword';

        $customerId = $this->createCustomerWithLegacyPassword($email, $password);
        $context = Context::createDefaultContext();

        $this->getBrowser()->jsonRequest(
            'PATCH',
            '/api/customer/' . $customerId,
            ['name' => 'Test']
        );

        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $customerId));

        /** @var CustomerEntity $customer */
        $customer = $this->customerRepository->search($criteria, $context)->first();

        static::assertNull($customer->getPassword());
        static::assertNotNull($customer->getLegacyPassword());
        static::assertNotNull($customer->getLegacyEncoder());

        $this->loginUser($email, $password);
    }

    private function loginUser(string $email, string $password): void
    {
        $this->browser
            ->jsonRequest(
                'POST',
                '/front-api/account/login',
                [
                    'email' => $email,
                    'password' => $password,
                ]
            );

        $response = $this->browser->getResponse();

        // After login successfully, the context token will be set in the header
        $contextToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
        static::assertNotEmpty($contextToken);
    }

    private function createCustomerWithLegacyPassword(string $email, string $password): string
    {
        $customerId = Uuid::randomHex();

        $customer = [
            'id' => $customerId,
            'channelId' => TestDefaults::CHANNEL,
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'email' => $email,
            'password' => null,
            'legacyPassword' => Hasher::hash($password, 'md5'),
            'legacyEncoder' => 'Md5',
            'nickname' => 'Mustermann',
            'customerNumber' => '12345',
        ];

        static::getContainer()->get('customer.repository')->create([$customer], Context::createDefaultContext());

        return $customerId;
    }
}
