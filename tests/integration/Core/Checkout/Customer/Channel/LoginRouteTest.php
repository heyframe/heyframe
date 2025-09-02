<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartPersister;
use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Checkout\Customer\Channel\LoginRoute;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\Exception\CustomerNotFoundException;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\ChannelApiTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\System\Channel\ContextTokenResponse;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @internal
 */
#[Package('checkout')]
#[Group('front-api')]
class LoginRouteTest extends TestCase
{
    use ChannelApiTestBehaviour;
    use IntegrationTestBehaviour;

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
            'id' => $this->ids->create('sales-channel'),
        ]);
        $this->assignChannelContext($this->browser);
        $this->customerRepository = static::getContainer()->get('customer.repository');
    }

    public function testInvalidCredentials(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/account/login',
                [
                    'email' => 'foo',
                    'password' => 'foo12345',
                ]
            );

        $response = json_decode((string) $this->browser->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('errors', $response);
        static::assertSame('Unauthorized', $response['errors'][0]['title']);
    }

    public function testEmptyRequest(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/account/login',
                [
                ]
            );

        $response = json_decode((string) $this->browser->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('errors', $response);
        static::assertSame('CHECKOUT__CUSTOMER_AUTH_BAD_CREDENTIALS', $response['errors'][0]['code']);
    }

    public function testValidLogin(): void
    {
        $email = Uuid::randomHex() . '@exämple.com';
        $this->createCustomer($email);

        $this->browser
            ->request(
                'POST',
                '/front-api/account/login',
                [
                    'email' => $email,
                    'password' => 'heyframe',
                ]
            );

        $response = $this->browser->getResponse();

        $contextToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
        static::assertNotEmpty($contextToken);
    }

    public function testItNotUpdatesCustomerLanguageIdOnValidLogin(): void
    {
        $email = Uuid::randomHex() . '@example.com';
        $customerId = $this->createCustomer($email, null, true, $this->getEnGbLanguageId());

        $this->browser
            ->request(
                'POST',
                '/front-api/account/login',
                [
                    'email' => $email,
                    'password' => 'heyframe',
                ],
            );

        static::assertSame(
            $this->getEnGbLanguageId(),
            $this->customerRepository->search(
                new Criteria([$customerId]),
                Context::createDefaultContext()
            )->getEntities()->first()?->getLanguageId()
        );
    }

    public function testValidLoginWithOneInactive(): void
    {
        $email = Uuid::randomHex() . '@example.com';
        // Inactive user with different password
        $this->createCustomer($email, null, false);
        // Active user with correct password
        $this->createCustomer($email);

        $this->browser
            ->request(
                'POST',
                '/front-api/account/login',
                [
                    'email' => $email,
                    'password' => 'heyframe',
                ]
            );

        $response = $this->browser->getResponse();

        $contextToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
        static::assertNotEmpty($contextToken);
    }

    public function testLoginWithInvalidBoundChannelId(): void
    {
        static::expectException(CustomerNotFoundException::class);

        $email = Uuid::randomHex() . '@example.com';
        $channel = $this->createChannel([
            'id' => Uuid::randomHex(),
        ]);

        $channelContext = $this->createChannelContext(Uuid::randomHex(), [
            'id' => Uuid::randomHex(),
        ], null);

        $this->createCustomer($email, $channel['id']);

        $loginRoute = static::getContainer()->get(LoginRoute::class);

        $requestDataBag = new RequestDataBag(['email' => $email, 'password' => 'heyframe']);

        $success = $loginRoute->login($requestDataBag, $channelContext);
        static::assertInstanceOf(ContextTokenResponse::class, $success);

        $loginRoute->login($requestDataBag, $channelContext);
    }

    public function testLoginSuccessRestoreCustomerContext(): void
    {
        $email = Uuid::randomHex() . '@example.com';
        $customerId = $this->createCustomer($email);
        $contextToken = Uuid::randomHex();

        $channelContext = $this->createChannelContext($contextToken, [], $customerId);
        $this->createCart($contextToken, $channelContext);

        $loginRoute = static::getContainer()->get(LoginRoute::class);

        $request = new RequestDataBag(['email' => $email, 'password' => 'heyframe']);

        $response = $loginRoute->login($request, $channelContext);

        // Token is replace as there're no customer token in the database
        static::assertNotSame($contextToken, $oldToken = $response->getToken());

        $channelContext = $this->createChannelContext('123456789', [], $customerId);

        $response = $loginRoute->login($request, $channelContext);

        // Previous token is restored
        static::assertSame($oldToken, $response->getToken());

        // Previous Cart is restored
        $channelContext = $this->createChannelContext($oldToken, [], $customerId);
        $oldCartExists = static::getContainer()->get(CartService::class)->getCart($oldToken, $channelContext);

        static::assertInstanceOf(Cart::class, $oldCartExists);
        static::assertSame($oldToken, $oldCartExists->getToken());
    }

    public function testCustomerHaveDifferentCartsOnEachChannel(): void
    {
        $email = Uuid::randomHex() . '@example.com';
        $customerId = $this->createCustomer($email);

        $this->createChannel([
            'id' => $this->ids->get('sales-channel-1'),
            'domains' => [
                [
                    'url' => 'http://test.de',
                    'currencyId' => Defaults::CURRENCY,
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'snippetSetId' => $this->getRandomId('snippet_set'),
                ],
            ],
        ]);

        $this->createChannel([
            'id' => $this->ids->get('sales-channel-2'),
            'domains' => [
                [
                    'url' => 'http://test.en',
                    'currencyId' => Defaults::CURRENCY,
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'snippetSetId' => $this->getRandomId('snippet_set'),
                ],
            ],
        ]);

        $channelContext1 = $this->createChannelContext($this->ids->get('context-1'), [], $customerId, $this->ids->get('sales-channel-1'));

        $channelContext2 = $this->createChannelContext($this->ids->get('context-2'), [], $customerId, $this->ids->get('sales-channel-2'));

        $this->createCart($this->ids->get('context-1'), $channelContext1);

        $this->createCart($this->ids->get('context-2'), $channelContext2);

        $loginRoute = static::getContainer()->get(LoginRoute::class);

        $request = new RequestDataBag(['email' => $email, 'password' => 'heyframe']);

        $responseChannel1 = $loginRoute->login($request, $channelContext1);

        $responseChannel2 = $loginRoute->login($request, $channelContext2);

        static::assertNotSame($responseChannel1->getToken(), $responseChannel2->getToken());

        $cartService = static::getContainer()->get(CartService::class);

        $cartFromChannel1 = $cartService->getCart($responseChannel1->getToken(), $channelContext1, false);
        $cartFromChannel2 = $cartService->getCart($responseChannel2->getToken(), $channelContext2, false);

        static::assertNotSame($cartFromChannel1->getToken(), $cartFromChannel2->getToken());
    }

    private function createCart(string $contextToken, ChannelContext $context): void
    {
        $persister = static::getContainer()->get(CartPersister::class);

        $persister->save(new Cart($contextToken), $context);
    }

    /**
     * @param array<string, mixed> $channelData
     */
    private function createChannelContext(string $contextToken, array $channelData, ?string $customerId, ?string $channelId = null): ChannelContext
    {
        if ($customerId) {
            $channelData[ChannelContextService::CUSTOMER_ID] = $customerId;
        }

        return static::getContainer()->get(ChannelContextFactory::class)->create(
            $contextToken,
            $channelId ?? TestDefaults::CHANNEL,
            $channelData
        );
    }

    private function createCustomer(?string $email = null, ?string $boundChannelId = null, bool $active = true, ?string $languageId = null): string
    {
        $customerId = Uuid::randomHex();

        $customer = [
            'id' => $customerId,
            'channelId' => TestDefaults::CHANNEL,
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'email' => $email,
            'password' => TestDefaults::HASHED_PASSWORD,
            'nickname' => 'Mustermann',
            'customerNumber' => '12345',
            'boundChannelId' => $boundChannelId,
            'active' => $active,
        ];

        if ($languageId !== null) {
            $customer['languageId'] = $languageId;
        }

        $this->customerRepository->create([$customer], Context::createDefaultContext());

        return $customerId;
    }
}
