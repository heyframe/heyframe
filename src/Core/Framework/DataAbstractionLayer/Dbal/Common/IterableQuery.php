<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\Common;

use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\QueryBuilder;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
interface IterableQuery
{
    /**
     * @return array<string|int, mixed>
     */
    public function fetch(): array;

    public function fetchCount(): int;

    public function getQuery(): QueryBuilder;

    /**
     * @return array{offset: int|null}
     */
    public function getOffset(): array;
}
