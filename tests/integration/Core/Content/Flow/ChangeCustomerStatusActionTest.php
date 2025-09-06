<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Content\Flow;

use HeyFrame\Core\Checkout\Cart\Rule\AlwaysValidRule;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\Event\CustomerLoginEvent;
use HeyFrame\Core\Content\Flow\Dispatching\Action\ChangeCustomerStatusAction;
use HeyFrame\Core\Content\Flow\FlowCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\ChannelApiTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\CountryAddToChannelTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @internal
 */
#[Package('after-sales')]
class ChangeCustomerStatusActionTest extends TestCase
{
    use ChannelApiTestBehaviour;
    use CountryAddToChannelTestBehaviour;
    use IntegrationTestBehaviour;

    /**
     * @var EntityRepository<FlowCollection>
     */
    private EntityRepository $flowRepository;

    private KernelBrowser $browser;

    private IdsCollection $ids;

    /**
     * @var EntityRepository<CustomerCollection>
     */
    private EntityRepository $customerRepository;

    protected function setUp(): void
    {
        $this->flowRepository = static::getContainer()->get('flow.repository');

        $this->customerRepository = static::getContainer()->get('customer.repository');

        $this->ids = new IdsCollection();

        $this->browser = $this->createCustomChannelBrowser([
            'id' => $this->ids->create('channel'),
        ]);

        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $this->ids->create('token'));
    }

    public function testChangeCustomerStatusAction(): void
    {
        $email = Uuid::randomHex() . '@example.com';
        $this->createCustomer($email);

        $sequenceId = Uuid::randomHex();
        $ruleId = Uuid::randomHex();

        $this->flowRepository->create([
            [
                'name' => 'Create Order',
                'eventName' => CustomerLoginEvent::EVENT_NAME,
                'priority' => 1,
                'active' => true,
                'sequences' => [
                    [
                        'id' => $sequenceId,
                        'parentId' => null,
                        'ruleId' => $ruleId,
                        'actionName' => null,
                        'config' => [],
                        'position' => 1,
                        'rule' => [
                            'id' => $ruleId,
                            'name' => 'Test rule',
                            'priority' => 1,
                            'conditions' => [
                                ['type' => (new AlwaysValidRule())->getName()],
                            ],
                        ],
                    ],
                    [
                        'id' => Uuid::randomHex(),
                        'parentId' => $sequenceId,
                        'ruleId' => null,
                        'actionName' => ChangeCustomerStatusAction::getName(),
                        'config' => [
                            'active' => false,
                        ],
                        'position' => 1,
                        'trueCase' => true,
                    ],
                ],
            ],
        ], Context::createDefaultContext());

        $this->login($email, 'heyframe');

        /** @var CustomerEntity $customer */
        $customer = $this->customerRepository->search(
            new Criteria([$this->ids->get('customer')]),
            Context::createDefaultContext()
        )->first();

        static::assertFalse($customer->getActive());
    }

    private function login(?string $email = null, ?string $password = null): void
    {
        $this->browser
            ->request(
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

        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $contextToken);
    }

    private function createCustomer(?string $email = null): void
    {
        $customer = [
            'id' => $this->ids->create('customer'),
            'channelId' => $this->ids->get('channel'),
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'email' => $email,
            'password' => TestDefaults::HASHED_PASSWORD,
            'nickname' => 'Mustermann',
            'customerNumber' => '12345',
            'active' => true,
        ];

        $this->customerRepository->create([$customer], Context::createDefaultContext());
    }
}
