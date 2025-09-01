<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\DataAbstractionLayer\Field\Flag;

use HeyFrame\Core\Framework\Api\Context\AdminApiSource;
use HeyFrame\Core\Framework\Api\Context\AdminChannelApiSource;
use HeyFrame\Core\Framework\Api\Context\ChannelApiSource;
use HeyFrame\Core\Framework\Api\Context\SystemSource;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\Log\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ApiAware::class)]
#[Package('framework')]
class ApiAwareTest extends TestCase
{
    public function testDefaultAllowsBothApis(): void
    {
        $flag = new ApiAware();

        static::assertTrue($flag->isBaseUrlAllowed('/api'));
        static::assertTrue($flag->isBaseUrlAllowed('/front-api'));

        static::assertTrue($flag->isSourceAllowed(AdminApiSource::class));
        static::assertTrue($flag->isSourceAllowed(ChannelApiSource::class));
        static::assertTrue($flag->isSourceAllowed(AdminChannelApiSource::class));
        static::assertTrue($flag->isSourceAllowed(SystemSource::class));
    }

    public function testOnlyAdminApiAware(): void
    {
        $flag = new ApiAware(AdminApiSource::class);

        static::assertTrue($flag->isBaseUrlAllowed('/api'));
        static::assertFalse($flag->isBaseUrlAllowed('/front-api'));

        static::assertTrue($flag->isSourceAllowed(AdminApiSource::class));
        static::assertFalse($flag->isSourceAllowed(ChannelApiSource::class));
        static::assertFalse($flag->isSourceAllowed(AdminChannelApiSource::class));
        static::assertTrue($flag->isSourceAllowed(SystemSource::class));
    }

    public function testOnlyStoreApiAware(): void
    {
        $flag = new ApiAware(ChannelApiSource::class);

        static::assertFalse($flag->isBaseUrlAllowed('/api'));
        static::assertTrue($flag->isBaseUrlAllowed('/front-api'));

        static::assertFalse($flag->isSourceAllowed(AdminApiSource::class));
        static::assertTrue($flag->isSourceAllowed(ChannelApiSource::class));
        static::assertTrue($flag->isSourceAllowed(AdminChannelApiSource::class));
        static::assertTrue($flag->isSourceAllowed(SystemSource::class));
    }
}
