<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Struct;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @codeCoverageIgnore
 *
 * @template-extends StoreCollection<BinaryStruct>
 */
#[Package('checkout')]
class BinaryCollection extends StoreCollection
{
    protected function getExpectedClass(): ?string
    {
        return BinaryStruct::class;
    }

    protected function getElementFromArray(array $element): StoreStruct
    {
        return BinaryStruct::fromArray($element);
    }
}
