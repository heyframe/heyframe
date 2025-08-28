<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\CrossSelling;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<CrossSellingElement>
 */
#[Package('inventory')]
class CrossSellingElementCollection extends Collection
{
    public function getApiAlias(): string
    {
        return 'cross_selling_elements';
    }

    protected function getExpectedClass(): ?string
    {
        return CrossSellingElement::class;
    }
}
