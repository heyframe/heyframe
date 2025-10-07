<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Profiling;

use HeyFrame\Core\Profiling\Profiling;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Profiling::class)]
class ProfilingTest extends TestCase
{
    public function testTemplatePriority(): void
    {
        $profiling = new Profiling();

        static::assertSame(-2, $profiling->getTemplatePriority());
    }
}
