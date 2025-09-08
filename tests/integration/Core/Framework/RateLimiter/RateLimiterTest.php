<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\RateLimiter;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use HeyFrame\Core\Checkout\Customer\Channel\AccountService;
use HeyFrame\Core\Checkout\Customer\Channel\LoginRoute;
use HeyFrame\Core\Framework\Api\Controller\AuthController as AdminAuthController;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\RateLimiter\RateLimiter;
use HeyFrame\Core\Framework\RateLimiter\RateLimiterFactory;
use HeyFrame\Core\Framework\Test\RateLimiter\DisableRateLimiterCompilerPass;
use HeyFrame\Core\Framework\Test\RateLimiter\RateLimiterTestTrait;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\Context\AbstractChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use HeyFrame\Core\Test\Integration\Traits\CustomerTestTrait;
use HeyFrame\Core\Test\Integration\Traits\OrderFixture;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestDefaults;
use League\OAuth2\Server\AuthorizationServer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\RateLimiter\Policy\NoLimiter;
use Symfony\Component\RateLimiter\Storage\CacheStorage;

/**
 * @internal
 */
#[CoversClass(RateLimiter::class)]
#[Group('slow')]
class RateLimiterTest extends TestCase
{
    use CustomerTestTrait;
    use OrderFixture;
    use RateLimiterTestTrait;

    private Context $context;

    private IdsCollection $ids;

    private KernelBrowser $browser;

    private AbstractChannelContextFactory $channelContextFactory;

    public static function setUpBeforeClass(): void
    {
        DisableRateLimiterCompilerPass::disableNoLimit();
        KernelLifecycleManager::bootKernel(true, Uuid::randomHex());
    }

    public static function tearDownAfterClass(): void
    {
        DisableRateLimiterCompilerPass::enableNoLimit();
        KernelLifecycleManager::bootKernel(true, Uuid::randomHex());
    }

    protected function setUp(): void
    {
        $this->context = Context::createDefaultContext();
        $this->ids = new IdsCollection();

        $this->browser = $this->createCustomChannelBrowser([
            'id' => $this->ids->create('sales-channel'),
        ]);
        $this->assignChannelContext($this->browser);

        $this->channelContextFactory = static::getContainer()->get(ChannelContextFactory::class)->getDecorated();

        $this->clearCache();
    }

    protected function tearDown(): void
    {
        DisableRateLimiterCompilerPass::enableNoLimit();
    }

    public function testRateLimitLoginRoute(): void
    {
        $email = Uuid::randomHex() . '@example.com';
        $password = 'wrongPassword';
        $this->createCustomer($email);

        for ($i = 0; $i <= 10; ++$i) {
            $this->browser
                ->request(
                    'POST',
                    '/front-api/account/login',
                    [
                        'email' => $email,
                        'password' => $password,
                    ]
                );

            $response = $this->browser->getResponse()->getContent();
            $response = json_decode((string) $response, true, 512, \JSON_THROW_ON_ERROR);

            static::assertArrayHasKey('errors', $response);

            if ($i >= 10) {
                static::assertSame(429, (int) $response['errors'][0]['status']);
                static::assertSame('CHECKOUT__CUSTOMER_AUTH_THROTTLED', $response['errors'][0]['code']);
            } else {
                static::assertSame(401, (int) $response['errors'][0]['status']);
                static::assertSame('Unauthorized', $response['errors'][0]['title']);
            }
        }
    }

    public function testResetRateLimitLoginRoute(): void
    {
        $route = new LoginRoute(
            static::getContainer()->get(AccountService::class),
            static::getContainer()->get('request_stack'),
            $this->mockResetLimiter([
                RateLimiter::LOGIN_ROUTE => 1,
            ])
        );

        $this->createCustomer('loginTest@example.com');

        static::getContainer()->get('request_stack')->push(new Request([
            'email' => 'loginTest@example.com',
            'password' => 'heyframe',
        ]));

        $route->login(new RequestDataBag([
            'email' => 'loginTest@example.com',
            'password' => 'heyframe',
        ]), $this->channelContextFactory->create(Uuid::randomHex(), TestDefaults::CHANNEL));
    }

    public function testRateLimitOauth(): void
    {
        for ($i = 0; $i <= 10; ++$i) {
            $this->browser
                ->request(
                    'POST',
                    '/api/oauth/token',
                    [
                        'grant_type' => 'password',
                        'client_id' => 'administration',
                        'username' => 'admin',
                        'password' => 'bla',
                    ]
                );

            $response = $this->browser->getResponse()->getContent();
            $response = json_decode((string) $response, true, 512, \JSON_THROW_ON_ERROR);

            static::assertArrayHasKey('errors', $response);

            if ($i >= 10) {
                static::assertSame(429, (int) $response['errors'][0]['status']);
                static::assertSame('FRAMEWORK__NOTIFICATION_THROTTLED', $response['errors'][0]['code']);
            } else {
                static::assertSame(400, (int) $response['errors'][0]['status']);
                static::assertSame('6', $response['errors'][0]['code']);
            }
        }
    }

    public function testResetRateLimitOauth(): void
    {
        $psrFactory = $this->createMock(PsrHttpFactory::class);
        $psrFactory->method('createRequest')->willReturn($this->createMock(ServerRequest::class));
        $psrFactory->method('createResponse')->willReturn($this->createMock(ResponseInterface::class));

        $authorizationServer = $this->createMock(AuthorizationServer::class);
        $authorizationServer->method('respondToAccessTokenRequest')->willReturn(new Response());

        $controller = new AdminAuthController(
            $authorizationServer,
            $psrFactory,
            $this->mockResetLimiter([
                RateLimiter::OAUTH => 1,
            ]),
        );

        $controller->token(new Request());
    }

    public function testItThrowsExceptionOnInvalidRoute(): void
    {
        $rateLimiter = new RateLimiter();

        $this->expectException(\RuntimeException::class);
        $rateLimiter->reset('test', 'test-key');
    }

    public function testIgnoreLimitWhenDisabled(): void
    {
        $config = [
            'enabled' => false,
            'id' => 'test_limit',
            'policy' => 'time_backoff',
            'reset' => '5 minutes',
            'limits' => [
                [
                    'limit' => 3,
                    'interval' => '10 seconds',
                ],
            ],
        ];

        $factory = new RateLimiterFactory(
            $config,
            new CacheStorage(new ArrayAdapter()),
            $this->createMock(SystemConfigService::class),
            $this->createMock(LockFactory::class),
        );

        static::assertInstanceOf(NoLimiter::class, $factory->create('example'));
    }
}
