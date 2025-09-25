<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\System\SystemConfig\Facade;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\Framework\Script\Execution\Script;
use HeyFrame\Core\Framework\Script\Execution\ScriptExecutor;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\Framework\Test\Script\Execution\ChannelTestHook;
use HeyFrame\Core\Framework\Test\Script\Execution\TestHook;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\SystemConfig\Facade\SystemConfigFacadeHookFactory;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use HeyFrame\Core\Test\Generator;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('framework')]
class SystemConfigFacadeTest extends TestCase
{
    use IntegrationTestBehaviour;

    private SystemConfigService $systemConfigService;

    private SystemConfigFacadeHookFactory $factory;

    protected function setUp(): void
    {
        $this->systemConfigService = static::getContainer()->get(SystemConfigService::class);
        $this->factory = static::getContainer()->get(SystemConfigFacadeHookFactory::class);
    }

    #[DataProvider('getWithoutAppCases')]
    public function testGetForScriptWithoutApp(Hook $hook, ?string $channelId, string $result): void
    {
        $this->systemConfigService->set('test.value', 'generic');
        $this->systemConfigService->set('test.value', 'specific', TestDefaults::CHANNEL);

        $facade = $this->factory->factory(
            $hook,
            new Script('test', '', new \DateTimeImmutable())
        );

        static::assertSame($result, $facade->get('test.value', $channelId));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function getWithoutAppCases(): array
    {
        $channelContext = Generator::generateChannelContext();
        $channelContext->getChannel()->setId(TestDefaults::CHANNEL);

        return [
            'simpleGet' => [
                new TestHook('test', Context::createDefaultContext()),
                null,
                'generic',
            ],
            'channelSpecificGet' => [
                new TestHook('test', Context::createDefaultContext()),
                TestDefaults::CHANNEL,
                'specific',
            ],
            'itUsesChannelFromChannelContextPerDefault' => [
                new ChannelTestHook('test', $channelContext),
                null,
                'specific',
            ],
            'overrideForChannelContext' => [
                new ChannelTestHook('test', $channelContext),
                Uuid::randomHex(), // as the value for this channel does not exist it falls back to the generic one
                'generic',
            ],
        ];
    }

    public function testGetAppConfigThrowsWithoutApp(): void
    {
        $this->systemConfigService->set('withSystemConfigPermission.config.testValue', 'test');

        $facade = $this->factory->factory(
            new TestHook('test', Context::createDefaultContext()),
            new Script('test', '', new \DateTimeImmutable())
        );

        static::expectException(\BadMethodCallException::class);
        $facade->app('testValue');
    }

    public function testSystemConfigIntegrationTest(): void
    {
        $this->systemConfigService->set('core.listing.productsPerPage', 'system_config');
        $this->systemConfigService->set('systemConfigExample.config.app_config', 'app_config');

        $page = new ArrayStruct();
        $hook = new TestHook(
            'test-config',
            Context::createDefaultContext(),
            [
                'page' => $page,
            ],
            [
                SystemConfigFacadeHookFactory::class,
            ]
        );

        static::getContainer()->get(ScriptExecutor::class)->execute($hook);

        static::assertTrue($page->hasExtension('systemConfigExtension'));
        $extension = $page->getExtension('systemConfigExtension');
        static::assertInstanceOf(ArrayStruct::class, $extension);

        static::assertSame('system_config', $extension->get('systemConfig'));
        static::assertSame('app_config', $extension->get('appConfig'));
    }
}
