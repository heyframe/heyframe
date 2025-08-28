<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<PriceDefinitionInterface>
 */
#[Package('checkout')]
class PriceDefinitionCollection extends Collection
{
    public function get($key): ?PriceDefinitionInterface
    {
        $key = (int) $key;

        if ($this->has($key)) {
            return $this->elements[$key];
        }

        return null;
    }

    public function getApiAlias(): string
    {
        return 'cart_price_definition_collection';
    }

    protected function getExpectedClass(): ?string
    {
        return PriceDefinitionInterface::class;
    }
}
