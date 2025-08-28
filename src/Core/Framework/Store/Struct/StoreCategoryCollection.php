<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Struct;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @codeCoverageIgnore
 *
 * @template-extends StoreCollection<StoreCategoryStruct>
 */
#[Package('checkout')]
class StoreCategoryCollection extends StoreCollection
{
    protected function getExpectedClass(): ?string
    {
        return StoreCategoryStruct::class;
    }

    protected function getElementFromArray(array $element): StoreStruct
    {
        return StoreCategoryStruct::fromArray($element);
    }
}
