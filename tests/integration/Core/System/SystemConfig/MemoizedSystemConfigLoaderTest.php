<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\System\SystemConfig;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use HeyFrame\Core\System\SystemConfig\CachedSystemConfigLoader;
use HeyFrame\Core\System\SystemConfig\ConfiguredSystemConfigLoader;
use HeyFrame\Core\System\SystemConfig\MemoizedSystemConfigLoader;
use HeyFrame\Core\System\SystemConfig\SystemConfigLoader;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('framework')]
class MemoizedSystemConfigLoaderTest extends TestCase
{
    use KernelTestBehaviour;

    public function testServiceDecorationChainPriority(): void
    {
        $service = static::getContainer()->get(SystemConfigLoader::class);

        static::assertInstanceOf(MemoizedSystemConfigLoader::class, $service);
        static::assertInstanceOf(ConfiguredSystemConfigLoader::class, $service->getDecorated());
        static::assertInstanceOf(CachedSystemConfigLoader::class, $service->getDecorated()->getDecorated());
        static::assertInstanceOf(SystemConfigLoader::class, $service->getDecorated()->getDecorated()->getDecorated());
    }
}
