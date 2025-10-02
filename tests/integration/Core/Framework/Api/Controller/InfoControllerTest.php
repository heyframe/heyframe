<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\Api\Controller;

use Doctrine\DBAL\Connection;
use HeyFrame\Administration\Controller\AdministrationController;
use HeyFrame\Administration\Framework\Twig\ViteFileAccessorDecorator;
use HeyFrame\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Customer\Event\CustomerLoginEvent;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Content\Flow\Api\FlowActionCollector;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\DevOps\Environment\EnvironmentHelper;
use HeyFrame\Core\Framework\Adapter\Messenger\Stamp\SentAtStamp;
use HeyFrame\Core\Framework\Api\ApiDefinition\DefinitionService;
use HeyFrame\Core\Framework\Api\Controller\InfoController;
use HeyFrame\Core\Framework\Api\Route\ApiRouteInfoResolver;
use HeyFrame\Core\Framework\Bundle;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\BusinessEventCollector;
use HeyFrame\Core\Framework\Event\ChannelAware;
use HeyFrame\Core\Framework\Event\CustomerAware;
use HeyFrame\Core\Framework\Event\CustomerGroupAware;
use HeyFrame\Core\Framework\Event\OrderAware;
use HeyFrame\Core\Framework\MessageQueue\Stats\StatsService;
use HeyFrame\Core\Framework\Plugin;
use HeyFrame\Core\Framework\Test\TestCaseBase\AdminFunctionalTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Kernel;
use HeyFrame\Core\Maintenance\System\Service\AppUrlVerifier;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use HeyFrame\Core\Test\Stub\Framework\BundleFixture;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\Stub\Symfony\StubKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;

/**
 * @internal
 */
class InfoControllerTest extends TestCase
{
    use AdminFunctionalTestBehaviour;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = static::getContainer()->get(Connection::class);
    }

    public function testGetConfig(): void
    {
        $expected = [
            'version' => '6.7.9999999.9999999-dev',
            'versionRevision' => str_repeat('0', 32),
            'adminWorker' => [
                'enableAdminWorker' => true,
                'enableQueueStatsWorker' => true,
                'enableNotificationWorker' => true,
                'transports' => ['async', 'low_priority'],
            ],
            'bundles' => [],
            'settings' => [
                'enableUrlFeature' => true,
                'appUrlReachable' => true,
                'appsRequireAppUrl' => false,
                'private_allowed_extensions' => [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                    'avif',
                    'gif',
                    'svg',
                    'bmp',
                    'tiff',
                    'tif',
                    'eps',
                    'webm',
                    'mkv',
                    'flv',
                    'ogv',
                    'ogg',
                    'mov',
                    'mp4',
                    'avi',
                    'wmv',
                    'pdf',
                    'aac',
                    'mp3',
                    'wav',
                    'flac',
                    'oga',
                    'wma',
                    'txt',
                    'doc',
                    'docx',
                    'ico',
                    'glb',
                    'zip',
                    'rar',
                    'csv',
                    'xls',
                    'xlsx',
                    'html',
                    'xml',
                ],
                'enableHtmlSanitizer' => true,
                'enableStagingMode' => false,
                'disableExtensionManagement' => false,
            ],
            'inAppPurchases' => [],
        ];

        $url = '/api/_info/config';
        $client = $this->getBrowser();
        $client->request(Request::METHOD_GET, $url);

        $content = $client->getResponse()->getContent();
        static::assertNotFalse($content);
        static::assertJson($content);

        $decodedResponse = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // reset environment-based mismatch
        $decodedResponse['bundles'] = [];
        $decodedResponse['versionRevision'] = $expected['versionRevision'];

        static::assertSame($expected, $decodedResponse);
    }

    public function testGetConfigWithPermissions(): void
    {
        $ids = new IdsCollection();

        $appUrl = EnvironmentHelper::getVariable('APP_URL');
        static::assertIsString($appUrl);

        $bundle = [
            'active' => true,
            'integrationId' => $ids->get('integration'),
            'type' => 'app',
            'baseUrl' => 'https://example.com',
            'permissions' => [
                'create' => ['user'],
                'read' => ['user'],
                'update' => ['user'],
                'delete' => ['user'],
                'additional' => ['user_change_me'],
            ],
            'version' => '1.0.0',
            'name' => 'PHPUnit',
        ];

        $expected = [
            'version' => Kernel::HEYFRAME_FALLBACK_VERSION,
            'versionRevision' => str_repeat('0', 32),
            'adminWorker' => [
                'enableAdminWorker' => true,
                'transports' => [],
            ],
            'bundles' => $bundle,
            'settings' => [
                'enableUrlFeature' => true,
                'enableHtmlSanitizer' => true,
            ],
        ];

        $url = '/api/_info/config';
        $client = $this->getBrowser();
        $client->request(Request::METHOD_GET, $url);

        $content = $client->getResponse()->getContent();
        static::assertNotFalse($content);
        static::assertJson($content);

        $decodedResponse = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        foreach (array_keys($expected) as $key) {
            static::assertArrayHasKey($key, $decodedResponse);
        }

        $bundles = $decodedResponse['bundles'];
        static::assertIsArray($bundles);
        static::assertArrayHasKey('PHPUnit', $bundles);
        static::assertIsArray($bundles['PHPUnit']);
        static::assertSame($bundle, $bundles['PHPUnit']);
    }

    public function testGetHeyFrameVersion(): void
    {
        $expected = [
            'version' => '6.7.9999999.9999999-dev',
        ];

        $url = '/api/_info/version';
        $client = $this->getBrowser();
        $client->request(Request::METHOD_GET, $url);

        $content = $client->getResponse()->getContent();
        static::assertNotFalse($content);
        static::assertJson($content);
        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $version = mb_substr(json_encode($expected, \JSON_THROW_ON_ERROR), 0, -3);
        static::assertNotEmpty($version);
        static::assertStringStartsWith($version, $content);
    }

    public function testGetHeyFrameVersionOldVersion(): void
    {
        $expected = [
            'version' => '6.7.9999999.9999999-dev',
        ];

        $url = '/api/v1/_info/version';
        $client = $this->getBrowser();
        $client->request(Request::METHOD_GET, $url);

        $content = $client->getResponse()->getContent();
        static::assertNotFalse($content);
        static::assertJson($content);
        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $version = mb_substr(json_encode($expected, \JSON_THROW_ON_ERROR), 0, -3);
        static::assertNotEmpty($version);
        static::assertStringStartsWith($version, $content);
    }

    public function testBusinessEventRoute(): void
    {
        $url = '/api/_info/events.json';
        $client = $this->getBrowser();
        $client->request(Request::METHOD_GET, $url);

        $content = $client->getResponse()->getContent();
        static::assertNotFalse($content);
        static::assertJson($content);

        $response = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $expected = [
            [
                'extensions' => [],
                'name' => 'checkout.customer.login',
                'class' => CustomerLoginEvent::class,
                'data' => [
                    'customer' => [
                        'type' => 'entity',
                        'entityClass' => CustomerDefinition::class,
                        'entityName' => 'customer',
                    ],
                    'contextToken' => [
                        'type' => 'string',
                    ],
                ],
                'aware' => [
                    ChannelAware::class,
                    lcfirst((new \ReflectionClass(ChannelAware::class))->getShortName()),
                    CustomerAware::class,
                    lcfirst((new \ReflectionClass(CustomerAware::class))->getShortName()),
                ],
            ],
            [
                'extensions' => [],
                'name' => 'checkout.order.placed',
                'class' => CheckoutOrderPlacedEvent::class,
                'data' => [
                    'order' => [
                        'type' => 'entity',
                        'entityClass' => OrderDefinition::class,
                        'entityName' => 'order',
                    ],
                ],
                'aware' => [
                    CustomerAware::class,
                    lcfirst((new \ReflectionClass(CustomerAware::class))->getShortName()),
                    CustomerGroupAware::class,
                    lcfirst((new \ReflectionClass(CustomerGroupAware::class))->getShortName()),
                    ChannelAware::class,
                    lcfirst((new \ReflectionClass(ChannelAware::class))->getShortName()),
                    OrderAware::class,
                    lcfirst((new \ReflectionClass(OrderAware::class))->getShortName()),
                ],
            ],
        ];

        foreach ($expected as $event) {
            $actualEvents = array_values(array_filter($response, static fn ($x) => $x['name'] === $event['name']));
            sort($event['aware']);
            sort($actualEvents[0]['aware']);
            static::assertNotEmpty($actualEvents, 'Event with name "' . $event['name'] . '" not found');
            static::assertCount(1, $actualEvents);
            static::assertSame($event, $actualEvents[0], $event['name']);
        }
    }

    public function testBundlePaths(): void
    {
        $kernel = new StubKernel([
            new BundleFixture('SomeFunctionalityBundle', __DIR__ . '/Fixtures/InfoController'),
        ]);

        $eventCollector = $this->createMock(FlowActionCollector::class);
        $infoController = new InfoController(
            $this->createMock(DefinitionService::class),
            new ParameterBag([
                'kernel.heyframe_version' => 'heyframe-version',
                'kernel.heyframe_version_revision' => 'heyframe-version-revision',
                'heyframe.admin_worker.enable_admin_worker' => 'enable-admin-worker',
                'heyframe.admin_worker.enable_queue_stats_worker' => 'enable-queue-stats-worker',
                'heyframe.admin_worker.enable_notification_worker' => 'enable-notification-worker',
                'heyframe.admin_worker.transports' => 'transports',
                'heyframe.filesystem.private_allowed_extensions' => ['png'],
                'heyframe.html_sanitizer.enabled' => true,
                'heyframe.media.enable_url_upload_feature' => true,
                'heyframe.staging.administration.show_banner' => true,
                'heyframe.deployment.runtime_extension_management' => true,
            ]),
            $kernel,
            $this->createMock(BusinessEventCollector::class),
            static::getContainer()->get('heyframe.increment.gateway.registry'),
            static::getContainer()->get(AppUrlVerifier::class),
            static::getContainer()->get('router'),
            $eventCollector,
            static::getContainer()->get(SystemConfigService::class),
            static::getContainer()->get(ApiRouteInfoResolver::class),
            new ViteFileAccessorDecorator(
                [],
                static::getContainer()->get('heyframe.asset.asset'),
                $kernel,
                new Filesystem(),
            ),
            new Filesystem(),
            $this->createMock(StatsService::class),
        );

        $infoController->setContainer($this->createMock(Container::class));

        $appUrl = EnvironmentHelper::getVariable('APP_URL');
        static::assertIsString($appUrl);

        $content = $infoController->config(Context::createDefaultContext(), Request::create($appUrl))->getContent();
        static::assertNotFalse($content);
        $config = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('SomeFunctionalityBundle', $config['bundles']);

        static::assertStringEndsWith(
            '/bundles/somefunctionality/administration/js/some-functionality-bundle.js',
            (string) $config['bundles']['SomeFunctionalityBundle']['js'][0]
        );
    }

    public function testBaseAdminPaths(): void
    {
        if (!class_exists(AdministrationController::class)) {
            static::markTestSkipped('Cannot test without Administration as results will differ');
        }

        $this->clearRequestStack();

        $kernel = new StubKernel([
            new AdminExtensionApiBundle(),
            new AdminExtensionApiWithoutSelfKnownBaseUrlBundle(),
            new AdminExtensionApiPlugin(true, __DIR__ . '/Fixtures/InfoController'),
            new AdminExtensionApiPluginWithLocalEntryPoint(true, __DIR__ . '/Fixtures/AdminExtensionApiPluginWithLocalEntryPoint'),
        ]);

        $eventCollector = $this->createMock(FlowActionCollector::class);

        $appUrl = EnvironmentHelper::getVariable('APP_URL');
        static::assertIsString($appUrl);

        $infoController = new InfoController(
            $this->createMock(DefinitionService::class),
            new ParameterBag([
                'kernel.heyframe_version' => 'heyframe-version',
                'kernel.heyframe_version_revision' => 'heyframe-version-revision',
                'heyframe.admin_worker.enable_admin_worker' => 'enable-admin-worker',
                'heyframe.admin_worker.enable_queue_stats_worker' => 'enable-queue-stats-worker',
                'heyframe.admin_worker.enable_notification_worker' => 'enable-notification-worker',
                'heyframe.admin_worker.transports' => 'transports',
                'heyframe.filesystem.private_allowed_extensions' => ['png'],
                'heyframe.html_sanitizer.enabled' => true,
                'heyframe.media.enable_url_upload_feature' => true,
                'heyframe.staging.administration.show_banner' => false,
                'heyframe.deployment.runtime_extension_management' => true,
            ]),
            $kernel,
            $this->createMock(BusinessEventCollector::class),
            static::getContainer()->get('heyframe.increment.gateway.registry'),
            static::getContainer()->get(AppUrlVerifier::class),
            static::getContainer()->get('router'),
            $eventCollector,
            static::getContainer()->get(SystemConfigService::class),
            static::getContainer()->get(ApiRouteInfoResolver::class),
            new ViteFileAccessorDecorator(
                [],
                static::getContainer()->get('heyframe.asset.asset'),
                $kernel,
                new Filesystem(),
            ),
            new Filesystem(),
            $this->createMock(StatsService::class),
        );

        $infoController->setContainer($this->createMock(Container::class));

        $content = $infoController->config(Context::createDefaultContext(), Request::create($appUrl))->getContent();
        static::assertNotFalse($content);
        $config = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertCount(4, $config['bundles']);

        static::assertArrayHasKey('AdminExtensionApiBundle', $config['bundles']);
        static::assertSame('https://extension-bundle.test', $config['bundles']['AdminExtensionApiBundle']['baseUrl']);
        static::assertSame('plugin', $config['bundles']['AdminExtensionApiBundle']['type']);

        static::assertArrayNotHasKey('AdminExtensionApiWithoutSelfKnownBaseUrlBundle', $config['bundles']);

        static::assertArrayHasKey('AdminExtensionApiPlugin', $config['bundles']);
        static::assertSame('https://extension-api.test', $config['bundles']['AdminExtensionApiPlugin']['baseUrl']);
        static::assertSame('plugin', $config['bundles']['AdminExtensionApiPlugin']['type']);

        static::assertArrayHasKey('AdminExtensionApiPluginWithLocalEntryPoint', $config['bundles']);
        static::assertStringContainsString(
            '/admin/adminextensionapipluginwithlocalentrypoint/index.html',
            $config['bundles']['AdminExtensionApiPluginWithLocalEntryPoint']['baseUrl'],
        );
        static::assertSame('plugin', $config['bundles']['AdminExtensionApiPluginWithLocalEntryPoint']['type']);

        static::assertArrayHasKey('AdminExtensionApiApp', $config['bundles']);
        static::assertSame('https://app-admin.test', $config['bundles']['AdminExtensionApiApp']['baseUrl']);
        static::assertSame('app', $config['bundles']['AdminExtensionApiApp']['type']);
    }

    public function testFlowActionsRoute(): void
    {
        $url = '/api/_info/flow-actions.json';
        $client = $this->getBrowser();
        $client->request(Request::METHOD_GET, $url);

        $content = $client->getResponse()->getContent();
        static::assertNotFalse($content);
        static::assertJson($content);

        $response = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $expected = [
            [
                'extensions' => [],
                'name' => 'action.add.order.tag',
                'requirements' => [
                    'orderAware',
                ],
                'delayable' => true,
            ],
        ];

        foreach ($expected as $action) {
            $actualActions = array_values(array_filter($response, static fn ($x) => $x['name'] === $action['name']));
            static::assertNotEmpty($actualActions, 'Event with name "' . $action['name'] . '" not found');
            static::assertCount(1, $actualActions);
            static::assertSame($action, $actualActions[0]);
        }
    }

    public function testFlowActionRouteHasAppFlowActions(): void
    {
        $aclRoleId = Uuid::randomHex();
        $this->createAclRole($aclRoleId);

        $url = '/api/_info/flow-actions.json';
        $client = $this->getBrowser();
        $client->request(Request::METHOD_GET, $url);

        $content = $client->getResponse()->getContent();
        static::assertNotFalse($content);
        static::assertJson($content);

        $response = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);

        $expected = [
            [
                'extensions' => [],
                'name' => 'telegram.send.message',
                'requirements' => [
                    'orderaware',
                ],
                'delayable' => true,
            ],
        ];

        foreach ($expected as $action) {
            $actualActions = array_values(array_filter($response, static fn ($x) => $x['name'] === $action['name']));
            static::assertNotEmpty($actualActions, 'Event with name "' . $action['name'] . '" not found');
            static::assertCount(1, $actualActions);
            static::assertSame($action, $actualActions[0]);
        }
    }

    public function testFetchApiRoutes(): void
    {
        $client = $this->getBrowser();
        $client->request(Request::METHOD_GET, '/api/_info/routes');

        $content = $client->getResponse()->getContent();
        static::assertNotFalse($content);
        static::assertJson($content);
        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $routes = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        foreach ($routes['endpoints'] as $route) {
            static::assertArrayHasKey('path', $route);
            static::assertArrayHasKey('methods', $route);
        }
    }

    public function testFetchMessageStats(): void
    {
        $statsService = $this->getContainer()->get(StatsService::class);
        $statsService->registerMessage(new Envelope(new \stdClass(), [
            new SentAtStamp(new \DateTimeImmutable('@' . (time() - 2))),
        ]));
        $statsService->registerMessage(new Envelope(new \stdClass(), [
            new SentAtStamp(new \DateTimeImmutable('@' . (time() - 1))),
        ]));

        $client = $this->getBrowser();
        $client->request(Request::METHOD_GET, '/api/_info/message-stats.json');

        $content = $client->getResponse()->getContent();
        static::assertNotFalse($content);
        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        static::assertJson($content);
        $stats = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);

        static::assertIsArray($stats);
        static::assertArrayHasKey('enabled', $stats);
        static::assertTrue($stats['enabled']);
        static::assertArrayHasKey('stats', $stats);
        static::assertIsArray($stats['stats']);
        static::assertArrayHasKey('totalMessagesProcessed', $stats['stats']);
        static::assertGreaterThanOrEqual(2, $stats['stats']['totalMessagesProcessed']);
        static::assertArrayHasKey('processedSince', $stats['stats']);
        static::assertInstanceOf(\DateTimeInterface::class, \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339_EXTENDED, $stats['stats']['processedSince']));
        static::assertArrayHasKey('averageTimeInQueue', $stats['stats']);
        static::assertIsFloat($stats['stats']['averageTimeInQueue']);
        static::assertArrayHasKey('messageTypeStats', $stats['stats']);
        static::assertIsArray($stats['stats']['messageTypeStats']);
        static::assertArrayHasKey('type', $stats['stats']['messageTypeStats'][0]);
        static::assertSame('stdClass', $stats['stats']['messageTypeStats'][0]['type']);
        static::assertArrayHasKey('count', $stats['stats']['messageTypeStats'][0]);
    }

    private function createAclRole(string $aclRoleId): void
    {
        $this->connection->insert('acl_role', [
            'id' => Uuid::fromHexToBytes($aclRoleId),
            'name' => 'aclTest',
            'privileges' => json_encode(['users_and_permissions.viewer'], \JSON_THROW_ON_ERROR),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }
}

/**
 * @internal
 */
class AdminExtensionApiBundle extends Bundle
{
    public function getAdminBaseUrl(): ?string
    {
        return 'https://extension-bundle.test';
    }
}

/**
 * @internal
 */
class AdminExtensionApiWithoutSelfKnownBaseUrlBundle extends Bundle
{
}

/**
 * @internal
 */
class AdminExtensionApiPlugin extends Plugin
{
    public function getAdminBaseUrl(): ?string
    {
        return 'https://extension-api.test';
    }
}

/**
 * @internal
 */
class AdminExtensionApiPluginWithLocalEntryPoint extends Plugin
{
    public function getPath(): string
    {
        $reflected = new \ReflectionObject($this);

        return \dirname($reflected->getFileName() ?: '') . '/Fixtures/AdminExtensionApiPluginWithLocalEntryPoint';
    }
}
