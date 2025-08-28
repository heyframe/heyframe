<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Struct;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @codeCoverageIgnore
 *
 * @template-extends StoreCollection<VariantStruct>
 */
#[Package('checkout')]
class VariantCollection extends StoreCollection
{
    protected function getExpectedClass(): ?string
    {
        return VariantStruct::class;
    }

    protected function getElementFromArray(array $element): StoreStruct
    {
        return VariantStruct::fromArray($element);
    }
}
