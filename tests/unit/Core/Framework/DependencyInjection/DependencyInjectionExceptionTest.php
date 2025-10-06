<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\DependencyInjection;

use HeyFrame\Core\Framework\DependencyInjection\DependencyInjectionException;
use HeyFrame\Core\Framework\Log\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('framework')]
#[CoversClass(DependencyInjectionException::class)]
class DependencyInjectionExceptionTest extends TestCase
{
    public function testProjectDirNotInContainer(): void
    {
        static::expectException(DependencyInjectionException::class);
        static::expectExceptionMessage('Container parameter "kernel.project_dir" needs to be a string');

        throw DependencyInjectionException::projectDirNotInContainer();
    }

    public function testBundlesMetadataIsNotAnArray(): void
    {
        static::expectException(DependencyInjectionException::class);
        static::expectExceptionMessage('Container parameter "kernel.bundles_metadata" needs to be an array');

        throw DependencyInjectionException::bundlesMetadataIsNotAnArray();
    }
}
