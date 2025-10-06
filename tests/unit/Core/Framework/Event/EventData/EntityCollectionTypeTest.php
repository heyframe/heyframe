<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Event\EventData;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Framework\Event\EventData\EntityCollectionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(EntityCollectionType::class)]
class EntityCollectionTypeTest extends TestCase
{
    public function testToArray(): void
    {
        $expected = [
            'type' => 'collection',
            'entityClass' => CustomerDefinition::class,
        ];

        static::assertSame($expected, (new EntityCollectionType(CustomerDefinition::class))->toArray());
    }
}
