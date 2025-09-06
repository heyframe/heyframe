<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Content\Flow;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\Rule\AlwaysValidRule;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\Event\CustomerLoginEvent;
use HeyFrame\Core\Content\Flow\Dispatching\Action\AddCustomerTagAction;
use HeyFrame\Core\Content\Flow\FlowCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
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
class AddCustomerTagActionTest extends TestCase
{
    use ChannelApiTestBehaviour;
    use CountryAddToChannelTestBehaviour;
    use IntegrationTestBehaviour;

    /**
     * @var EntityRepository<FlowCollection>
     */
    private EntityRepository $flowRepository;

    private Connection $connection;

    private KernelBrowser $browser;

    private IdsCollection $ids;

    /**
     * @var EntityRepository<CustomerCollection>
     */
    private EntityRepository $customerRepository;

    protected function setUp(): void
    {
        $this->flowRepository = static::getContainer()->get('flow.repository');

        $this->connection = static::getContainer()->get(Connection::class);

        $this->customerRepository = static::getContainer()->get('customer.repository');

        $this->ids = new IdsCollection();

        $this->browser = $this->createCustomChannelBrowser([
            'id' => $this->ids->create('channel'),
        ]);

        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $this->ids->create('token'));
    }

    public function testAddCustomerTagAction(): void
    {
        $this->createDataTest();

        $email = Uuid::randomHex() . '@example.com';
        $this->createCustomer($email);

        $sequenceId = Uuid::randomHex();
        $ruleId = Uuid::randomHex();

        $this->flowRepository->create([[
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
                    'actionName' => AddCustomerTagAction::getName(),
                    'config' => [
                        'tagIds' => [
                            $this->ids->get('tag_id') => 'test tag',
                            $this->ids->get('tag_id2') => 'test tag2',
                        ],
                    ],
                    'position' => 1,
                    'trueCase' => true,
                ],
                [
                    'id' => Uuid::randomHex(),
                    'parentId' => $sequenceId,
                    'ruleId' => null,
                    'actionName' => AddCustomerTagAction::getName(),
                    'config' => [
                        'tagIds' => [
                            $this->ids->get('tag_id3') => 'test tag3',
                        ],
                    ],
                    'position' => 1,
                    'trueCase' => true,
                ],
            ],
        ]], Context::createDefaultContext());

        $this->login($email, 'heyframe');

        $customerTag = $this->connection->fetchAllAssociative(
            'SELECT tag_id FROM customer_tag WHERE tag_id IN (:ids)',
            ['ids' => [Uuid::fromHexToBytes($this->ids->get('tag_id')), Uuid::fromHexToBytes($this->ids->get('tag_id2')), Uuid::fromHexToBytes($this->ids->get('tag_id3'))]],
            ['ids' => ArrayParameterType::BINARY]
        );

        static::assertCount(3, $customerTag);
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
        ];

        $this->customerRepository->create([$customer], Context::createDefaultContext());
    }

    private function createDataTest(): void
    {
        static::getContainer()->get('tag.repository')->create([
            [
                'id' => $this->ids->create('tag_id'),
                'name' => 'test tag',
            ],
            [
                'id' => $this->ids->create('tag_id2'),
                'name' => 'test tag2',
            ],
            [
                'id' => $this->ids->create('tag_id3'),
                'name' => 'test tag3',
            ],
        ], Context::createDefaultContext());
    }
}
