<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Administration\RateLimiter;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Test\RateLimiter\DisableRateLimiterCompilerPass;
use HeyFrame\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use HeyFrame\Core\Test\Integration\Traits\CustomerTestTrait;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Group('slow')]
class RateLimiterTest extends TestCase
{
    use AdminApiTestBehaviour;
    use CustomerTestTrait;

    private Context $context;

    public static function setUpBeforeClass(): void
    {
        DisableRateLimiterCompilerPass::disableNoLimit();
    }

    public static function tearDownAfterClass(): void
    {
        DisableRateLimiterCompilerPass::enableNoLimit();
    }

    protected function setUp(): void
    {
        $this->context = Context::createDefaultContext();
    }

    protected function tearDown(): void
    {
        DisableRateLimiterCompilerPass::enableNoLimit();
    }

    public function testRateLimitNotificationRoute(): void
    {
        $ids = new IdsCollection();
        $integrationId = $ids->create('integration');
        $client = $this->getBrowserAuthenticatedWithIntegration($integrationId);

        $url = '/api/notification';
        $data = [
            'status' => 'success',
            'message' => 'This is a notification',
        ];

        for ($i = 0; $i <= 10; ++$i) {
            $client->jsonRequest('POST', $url, $data);

            $response = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

            if ($i >= 10) {
                static::assertArrayHasKey('errors', $response);
                static::assertSame(Response::HTTP_TOO_MANY_REQUESTS, (int) $response['errors'][0]['status']);
                static::assertSame('FRAMEWORK__NOTIFICATION_THROTTLED', $response['errors'][0]['code']);
            } else {
                static::assertSame(200, $client->getResponse()->getStatusCode());
            }
        }
    }
}
