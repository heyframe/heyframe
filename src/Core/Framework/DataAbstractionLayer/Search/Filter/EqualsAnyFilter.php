<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class EqualsAnyFilter extends SingleFieldFilter
{
    /**
     * @param list<string|int|float|null>|array<string, string> $value
     */
    public function __construct(
        protected readonly string $field,
        protected array $value = []
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return list<string|int|float|null>|array<string, string>
     */
    public function getValue(): array
    {
        return $this->value;
    }

    public function getFields(): array
    {
        return [$this->field];
    }
}
