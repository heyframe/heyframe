<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Event\EventData;

use HeyFrame\Core\Framework\Event\EventData\ArrayType;
use HeyFrame\Core\Framework\Event\EventData\ScalarValueType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ArrayType::class)]
class ArrayTypeTest extends TestCase
{
    public function testToArray(): void
    {
        $expected = [
            'type' => 'array',
            'of' => [
                'type' => 'string',
            ],
        ];

        static::assertSame(
            $expected,
            (new ArrayType(new ScalarValueType(ScalarValueType::TYPE_STRING)))
                ->toArray()
        );
    }
}
