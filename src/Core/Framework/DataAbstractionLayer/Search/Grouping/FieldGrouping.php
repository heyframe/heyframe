<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\Grouping;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\CriteriaPartInterface;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @final
 */
#[Package('framework')]
class FieldGrouping extends Struct implements CriteriaPartInterface
{
    public function __construct(protected readonly string $field)
    {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getFields(): array
    {
        return [$this->field];
    }
}
