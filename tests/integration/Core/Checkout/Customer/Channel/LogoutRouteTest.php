<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Customer\Channel;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Customer\Channel\LoginRoute;
use HeyFrame\Core\Checkout\Customer\Channel\LogoutRoute;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\RoutingException;
use HeyFrame\Core\Framework\Test\TestCaseBase\ChannelApiTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Util\Random;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\System\Channel\ContextTokenResponse;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use HeyFrame\Core\Test\Integration\Traits\CustomerTestTrait;
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
class LogoutRouteTest extends TestCase
{
    use ChannelApiTestBehaviour;
    use CustomerTestTrait;
    use IntegrationTestBehaviour;

    private KernelBrowser $browser;

    private IdsCollection $ids;

    protected function setUp(): void
    {
        $this->ids = new IdsCollection();

        $this->browser = $this->createCustomChannelBrowser([
            'id' => $this->ids->create('channel'),
        ]);
        $this->assignChannelContext($this->browser);
    }

    public function testNotLoggedin(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/account/logout',
            );

        static::assertIsString($this->browser->getResponse()->getContent());
        $response = json_decode($this->browser->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('errors', $response);
        static::assertSame(RoutingException::CUSTOMER_NOT_LOGGED_IN_CODE, $response['errors'][0]['code']);
    }

    public function testValidLogout(): void
    {
        $email = Uuid::randomHex() . '@example.com';
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

        static::assertIsString($this->browser->getResponse()->getContent());

        $response = $this->browser->getResponse();

        // After login successfully, the context token will be set in the header
        $contextToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
        static::assertNotEmpty($contextToken);

        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $contextToken);

        $this->browser
            ->request(
                'POST',
                '/front-api/account/logout',
            );

        static::assertSame(200, $this->browser->getResponse()->getStatusCode());

        $this->browser
            ->request(
                'POST',
                '/front-api/account/customer'
            );

        static::assertIsString($this->browser->getResponse()->getContent());
        $response = json_decode($this->browser->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('errors', $response);
    }

    public function testLogoutKeepsCartToBeAbleToRestore(): void
    {
        $email = Uuid::randomHex() . '@example.com';
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

        static::assertIsString($this->browser->getResponse()->getContent());

        $response = $this->browser->getResponse();

        // After login successfully, the context token will be set in the header
        $contextToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
        static::assertNotEmpty($contextToken);

        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $contextToken);

        $this->browser
            ->request(
                'POST',
                '/front-api/account/logout',
            );

        static::assertSame(200, $this->browser->getResponse()->getStatusCode());

        $tokens = static::getContainer()->get(Connection::class)
            ->fetchFirstColumn('SELECT token FROM channel_api_context WHERE customer_id =  (SELECT id FROM customer where email = ?)', [$email]);

        static::assertCount(1, $tokens);
        static::assertNotContains($contextToken, $tokens, 'Old token should still exist');
    }

    public function testLoggedOutKeepCustomerContextWithoutReplaceTokenParameter(): void
    {
        $systemConfig = static::getContainer()->get(SystemConfigService::class);
        $systemConfig->set('core.loginRegistration.invalidateSessionOnLogOut', false);

        $email = Uuid::randomHex() . '@example.com';
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

        static::assertIsString($this->browser->getResponse()->getContent());

        $response = $this->browser->getResponse();

        $currentCustomerToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?: '';

        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $currentCustomerToken);

        $this->browser
            ->request(
                'POST',
                '/front-api/account/logout',
            );

        $customerIdWithOldToken = static::getContainer()->get(Connection::class)->fetchOne('SELECT customer_id FROM channel_api_context WHERE token = ?', [$currentCustomerToken]);
        static::assertFalse($customerIdWithOldToken, 'The old token should be gone');
    }

    public function testLogoutRouteReturnContextTokenResponse(): void
    {
        $systemConfig = static::getContainer()->get(SystemConfigService::class);
        $systemConfig->set('core.loginRegistration.invalidateSessionOnLogOut', false);

        $email = Uuid::randomHex() . '@example.com';
        $this->createCustomer($email);

        $contextToken = Random::getAlphanumericString(32);

        $channelContext = static::getContainer()->get(ChannelContextFactory::class)->create(
            $contextToken,
            TestDefaults::CHANNEL,
            []
        );

        $request = new RequestDataBag(['email' => $email, 'password' => 'heyframe']);
        $loginResponse = static::getContainer()->get(LoginRoute::class)->login($request, $channelContext);

        $customerId = $this->createCustomer();
        $customer = static::getContainer()
            ->get('customer.repository')
            ->search(new Criteria(), Context::createDefaultContext())
            ->get($customerId);
        static::assertInstanceOf(CustomerEntity::class, $customer);
        $channelContext->assign([
            'token' => $loginResponse->getToken(),
            'customer' => $customer,
        ]);

        $logoutResponse = static::getContainer()->get(LogoutRoute::class)->logout(
            $channelContext,
            new RequestDataBag()
        );

        static::assertInstanceOf(ContextTokenResponse::class, $logoutResponse);
        static::assertNotSame($loginResponse->getToken(), $logoutResponse->getToken());
    }
}
