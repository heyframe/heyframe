<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Test\Category\Service;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearcherInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;

/**
 * @internal
 */
class CountingEntitySearcher implements EntitySearcherInterface
{
    /**
     * @var int[]
     */
    private static array $count = [];

    public function __construct(private readonly EntitySearcherInterface $inner)
    {
    }

    public function search(EntityDefinition $definition, Criteria $criteria, Context $context): IdSearchResult
    {
        static::$count[$definition->getEntityName()] ??= 0 + 1;

        return $this->inner->search($definition, $criteria, $context);
    }

    public static function resetCount(): void
    {
        static::$count = [];
    }

    public static function getSearchOperationCount(string $entityName): int
    {
        return static::$count[$entityName] ?? 0;
    }
}
