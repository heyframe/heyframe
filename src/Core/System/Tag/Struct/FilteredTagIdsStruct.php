<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Tag\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('fundamentals@framework')]
class FilteredTagIdsStruct extends Struct
{
    /**
     * @param array<string> $ids
     */
    public function __construct(
        protected array $ids,
        protected int $total
    ) {
    }

    /**
     * @return array<string>
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
