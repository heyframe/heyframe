<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Event\EventData;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Framework\Event\EventData\EntityType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(EntityType::class)]
class EntityTypeTest extends TestCase
{
    public function testToArray(): void
    {
        $definition = CustomerDefinition::class;

        $expected = [
            'type' => 'entity',
            'entityClass' => CustomerDefinition::class,
            'entityName' => 'customer',
        ];

        static::assertSame($expected, (new EntityType($definition))->toArray());
        static::assertSame($expected, (new EntityType(new CustomerDefinition()))->toArray());
    }
}
