<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\Context\ChannelContextPersister;
use HeyFrame\Core\Test\Integration\Traits\CustomerTestTrait;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @internal
 */
#[Package('checkout')]
#[Group('front-api')]
class ChangePasswordRouteTest extends TestCase
{
    use CustomerTestTrait;
    use IntegrationTestBehaviour;

    private KernelBrowser $browser;

    private IdsCollection $ids;

    private string $email;

    private string $contextToken;

    private string $customerId;

    protected function setUp(): void
    {
        $this->ids = new IdsCollection();

        $this->browser = $this->createCustomChannelBrowser([
            'id' => $this->ids->create('sales-channel'),
        ]);
        $this->assignChannelContext($this->browser);

        $this->email = Uuid::randomHex() . '@example.com';
        $this->customerId = $this->createCustomer($this->email);

        $this->browser
            ->request(
                'POST',
                '/front-api/account/login',
                [
                    'email' => $this->email,
                    'password' => 'heyframe',
                ]
            );

        $response = $this->browser->getResponse();

        $this->contextToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
        static::assertNotEmpty($this->contextToken);

        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $this->contextToken);
    }

    public function testEmptyRequest(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/account/change-password',
                [
                ]
            );

        $response = json_decode((string) $this->browser->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('errors', $response);
        static::assertSame('VIOLATION::IS_BLANK_ERROR', $response['errors'][0]['code']);
    }

    public function testChangeInvalidPassword(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/account/change-password',
                [
                    'password' => 'foooware',
                    'newPassword' => 'foooware',
                ]
            );

        $response = json_decode((string) $this->browser->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('errors', $response);
        static::assertSame('VIOLATION::CUSTOMER_PASSWORD_NOT_CORRECT', $response['errors'][0]['code']);
    }

    public function testChangeAndLogin(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/account/change-password',
                [
                    'password' => 'heyframe',
                    'newPassword' => 'foooware',
                    'newPasswordConfirm' => 'foooware',
                ]
            );

        $response = $this->browser->getResponse();

        $responseContent = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayNotHasKey('errors', $responseContent);

        $contextToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
        static::assertNotEmpty($contextToken);

        $this->browser
            ->request(
                'POST',
                '/front-api/account/login',
                [
                    'email' => $this->email,
                    'password' => 'foooware',
                ]
            );

        $response = $this->browser->getResponse();

        $responseContent = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayNotHasKey('errors', $responseContent);

        $contextToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
        static::assertNotEmpty($contextToken);
    }

    public function testContextTokenIsReplacedAfterChangingPassword(): void
    {
        $this->browser
            ->request(
                'POST',
                '/front-api/account/change-password',
                [
                    'password' => 'heyframe',
                    'newPassword' => 'foooware',
                    'newPasswordConfirm' => 'foooware',
                ]
            );

        $response = $this->browser->getResponse();

        $contextToken = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN) ?? '';
        static::assertNotEmpty($contextToken);

        $oldContextExists = static::getContainer()->get(ChannelContextPersister::class)->load($this->contextToken, $this->ids->get('sales-channel'));

        static::assertEmpty($oldContextExists);

        // Token is replaced
        static::assertNotSame($this->contextToken, $contextToken);

        $newContextExists = static::getContainer()->get(ChannelContextPersister::class)->load($contextToken, $this->ids->get('sales-channel'), $this->customerId);

        static::assertNotEmpty($newContextExists);
    }
}
