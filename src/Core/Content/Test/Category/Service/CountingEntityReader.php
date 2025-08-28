<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Test\Category\Service;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Read\EntityReaderInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;

/**
 * @internal
 */
class CountingEntityReader implements EntityReaderInterface
{
    /**
     * @var int[]
     */
    private static array $count = [];

    public function __construct(private readonly EntityReaderInterface $inner)
    {
    }

    /**
     * @return EntityCollection<Entity>
     */
    public function read(EntityDefinition $definition, Criteria $criteria, Context $context): EntityCollection
    {
        self::$count[$definition->getEntityName()] ??= 0 + 1;

        return $this->inner->read($definition, $criteria, $context);
    }

    public static function resetCount(): void
    {
        self::$count = [];
    }

    public static function getReadOperationCount(string $entityName): int
    {
        return self::$count[$entityName] ?? 0;
    }
}
