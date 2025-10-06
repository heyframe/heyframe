<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Event\EventData;

use HeyFrame\Core\Framework\Event\EventData\ScalarValueType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ScalarValueType::class)]
class ScalarValueTypeTest extends TestCase
{
    public function testToArray(): void
    {
        $expected = [
            'type' => 'float',
        ];

        static::assertSame($expected, (new ScalarValueType(ScalarValueType::TYPE_FLOAT))->toArray());
    }

    public function testThrowExceptionOnInvalidType(): void
    {
        static::expectException(\InvalidArgumentException::class);

        new ScalarValueType('test');
    }
}
