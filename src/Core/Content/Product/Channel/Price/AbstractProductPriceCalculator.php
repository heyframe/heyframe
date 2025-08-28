<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Price;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\Service\ResetInterface;

#[Package('inventory')]
abstract class AbstractProductPriceCalculator implements ResetInterface
{
    public function reset(): void
    {
        $this->getDecorated()->reset();
    }

    abstract public function getDecorated(): AbstractProductPriceCalculator;

    /**
     * @param Entity[] $products
     */
    abstract public function calculate(iterable $products, ChannelContext $context): void;
}
