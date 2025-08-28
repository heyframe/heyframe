<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Search;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @internal
 */
#[Package('checkout')]
abstract class FilterStruct extends Struct
{
    protected string $type;

    /**
     * @return EqualsFilterStruct|MultiFilterStruct
     */
    public static function fromArray(array $data): FilterStruct
    {
        $type = $data['type'];

        if ($type === 'multi') {
            return MultiFilterStruct::fromArray($data);
        }

        if ($type === 'equals') {
            return EqualsFilterStruct::fromArray($data);
        }

        throw new \InvalidArgumentException('Type ' . $type . ' not allowed');
    }

    /**
     * @return array<string, string>
     */
    abstract public function getQueryParameter(): array;

    public function getType(): string
    {
        return $this->type;
    }
}
